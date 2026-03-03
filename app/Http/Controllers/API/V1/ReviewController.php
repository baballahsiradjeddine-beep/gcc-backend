<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Models\UserMistake;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends BaseController
{
    /**
     * Get questions scheduled for review today.
     */
    public function getTodayReview(Request $request)
    {
        $user = $request->user();
        
        $mistakes = UserMistake::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->where('mastery_level', '<', 100)
            ->with('question')
            ->orderBy('next_review_at', 'asc')
            ->limit(15)
            ->get();

        $questions = $mistakes->map(function ($mistake) {
            $q = $mistake->question;
            if ($q) {
                $q->mistake_info = [
                    'mistake_count' => $mistake->mistake_count,
                    'mastery_level' => $mistake->mastery_level,
                ];
            }
            return $q;
        })->filter();

        return $this->sendResponse([
            'questions' => $questions,
            'count' => $questions->count(),
            'total_pending' => UserMistake::where('user_id', $user->id)
                ->where('next_review_at', '<=', now())
                ->where('mastery_level', '<', 100)
                ->count(),
        ]);
    }

    /**
     * Submit results for a review session.
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'results' => 'required|array',
            'results.*.question_id' => 'required|exists:questions,id',
            'results.*.is_correct' => 'required|boolean',
        ]);

        $user = $request->user();
        $results = $request->results;
        $processed = 0;

        foreach ($results as $res) {
            $mistake = UserMistake::where('user_id', $user->id)
                ->where('question_id', $res['question_id'])
                ->first();

            if (!$mistake) continue;

            if ($res['is_correct']) {
                $mistake->correct_at_review_count += 1;
                $mistake->mastery_level = min(100, $mistake->mastery_level + 25);
                
                // Spaced Repetition Logic (Days: 1, 3, 7, 14, 30)
                $intervals = [0 => 1, 1 => 3, 2 => 7, 3 => 14, 4 => 30];
                $currentStreak = min(4, $mistake->correct_at_review_count - 1);
                $daysToAdd = $intervals[$currentStreak] ?? 30;
                
                $mistake->next_review_at = now()->addDays($daysToAdd)->startOfDay();
            } else {
                $mistake->mistake_count += 1;
                $mistake->mastery_level = max(0, $mistake->mastery_level - 15);
                $mistake->next_review_at = now()->addDay()->startOfDay(); // Review again tomorrow
            }

            $mistake->save();
            $processed++;
        }

        return $this->sendResponse([
            'processed' => $processed,
            'message' => 'Review results processed successfully'
        ]);
    }
}
