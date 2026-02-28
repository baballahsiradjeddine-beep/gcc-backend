<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\API\BaseController;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestNotification;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SocialController extends BaseController
{
    /**
     * Search for users to add as friends.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('q');
        $currentUser = $request->user();

        if (strlen($query) < 2) {
            return $this->sendResponse([], 'يرجى كتابة حرفين على الأقل للبحث.');
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->where('id', '!=', $currentUser->id)
            ->take(20)
            ->get(['id', 'name', 'avatar_url']);

        // Attach relationship status for each user
        $users->each(function ($user) use ($currentUser) {
            $friendship = Friendship::where(function ($q) use ($currentUser, $user) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
            })->orWhere(function ($q) use ($currentUser, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
            })->first();

            $user->friendship_status = $friendship ? $friendship->status : 'none';
            // Also identify if the current user is the one who sent the request
            $user->is_sender = $friendship && $friendship->sender_id === $currentUser->id;
        });

        return $this->sendResponse($users, 'تم جلب نتائج البحث');
    }

    /**
     * Send a friend request.
     */
    public function sendFriendRequest(Request $request)
    {
        $sender = $request->user();
        $receiverId = $request->receiver_id;

        if ($sender->id == $receiverId) {
            return $this->sendError('لا يمكنك إضافة نفسك كصديق.', [], 400);
        }

        $existing = Friendship::where(function ($q) use ($sender, $receiverId) {
            $q->where('sender_id', $sender->id)->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($sender, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $sender->id);
        })->first();

        if ($existing) {
            return $this->sendError('يوجد طلب بالفعل أو أنكم أصدقاء.', [], 400);
        }

        $friendship = Friendship::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'status' => 'pending',
        ]);

        // Notify the receiver (Database)
        $receiver = User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new FriendRequestNotification($sender));
            
            // Push Notification (FCM)
            if ($receiver->fcm_token) {
                FCMService::send(
                    $receiver->fcm_token,
                    'طلب صداقة جديد 🤝',
                    "أرسل لك {$sender->name} طلب صداقة جديد.",
                    ['type' => 'friend_request', 'sender_id' => (string)$sender->id],
                    $receiver->id
                );
            }
        }

        return $this->sendResponse($friendship, 'تم إرسال طلب الصداقة بنجاح.');
    }

    /**
     * Accept a friend request.
     */
    public function acceptFriendRequest(Request $request)
    {
        $user = $request->user();
        $requestId = $request->request_id;

        $friendship = Friendship::where('id', $requestId)
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return $this->sendError('الطلب غير موجود أو تمت معالجته بالفعل.', [], 404);
        }

        $friendship->update(['status' => 'accepted']);

        // Notify the sender that the request was accepted
        $sender = User::find($friendship->sender_id);
        if ($sender && $sender->fcm_token) {
            FCMService::send(
                $sender->fcm_token,
                'تم قبول طلب الصداقة 🎉',
                "وافق {$user->name} على طلب الصداقة، أنتما الآن أصدقاء!",
                ['type' => 'friend_accepted', 'receiver_id' => (string)$user->id],
                $sender->id
            );
        }

        return $this->sendResponse($friendship, 'تم قبول طلب الصداقة! أنتما الآن أصدقاء 🎉');
    }

    /**
     * Reject or cancel a friend request.
     */
    public function rejectFriendRequest(Request $request)
    {
        $user = $request->user();
        $requestId = $request->request_id;

        $friendship = Friendship::where('id', $requestId)
            ->where(function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->orWhere('sender_id', $user->id);
            })
            ->first();

        if (!$friendship) {
            return $this->sendError('الطلب غير موجود.', [], 404);
        }

        $friendship->delete();

        return $this->sendResponse(null, 'تم إلغاء/رفض الطلب.');
    }

    /**
     * Get friend list.
     */
    public function getFriends(Request $request)
    {
        $user = $request->user();

        $friendships = Friendship::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->where('status', 'accepted')
            ->get();

        $friends = $friendships->map(function ($f) use ($user) {
            $friend = ($f->sender_id == $user->id) ? $f->receiver : $f->sender;
            return [
                'id' => $friend->id,
                'name' => $friend->name,
                'avatar_url' => $friend->avatar_url,
                'friendship_id' => $f->id,
            ];
        });

        return $this->sendResponse($friends, 'تم جلب قائمة الأصدقاء.');
    }

    /**
     * Get pending friend requests.
     */
    public function getPendingRequests(Request $request)
    {
        $user = $request->user();

        $requests = Friendship::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender:id,name,avatar_url')
            ->get();

        return $this->sendResponse($requests, 'تم جلب طلبات الصداقة المعلقة.');
    }

    /**
     * Send a challenge invitation via Push Notification.
     */
    public function sendChallengeInvitation(Request $request)
    {
        $sender = $request->user();
        $receiverId = $request->receiver_id;
        $unitId = $request->unit_id;
        $courseTitle = $request->course_title;

        $receiver = User::find($receiverId);
        if (!$receiver || !$receiver->fcm_token) {
            return $this->sendError('المنافس غير متاح حالياً لاستلام الإشعارات.', [], 404);
        }

        // Send Push Notification for Challenge
        FCMService::send(
            $receiver->fcm_token,
            'تحدي جديد ⚔️',
            "يتحداك {$sender->name} في مادة {$courseTitle}!",
            [
                'type' => 'challenge_invite',
                'sender_id' => (string)$sender->id,
                'sender_name' => $sender->name,
                'unit_id' => (string)$unitId,
                'course_title' => $courseTitle,
                'invitation_code' => $request->invitation_code, // Pass the generated code
                'screen' => 'social_challenge'
            ],
            $receiver->id
        );

        return $this->sendResponse(null, 'تم إرسال دعوة التحدي بنجاح.');
    }
}
