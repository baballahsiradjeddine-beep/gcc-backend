<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutomatedNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SendAutomatedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-automated-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends automated push notifications based on predefined triggers (streaks, inactivity, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeNotifications = AutomatedNotification::where('is_active', true)->get();

        if ($activeNotifications->isEmpty()) {
            $this->info('No active automated notifications found.');
            return;
        }

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $twoDaysAgo = Carbon::now()->subDays(2)->toDateString();
        $threeDaysAgo = Carbon::now()->subDays(3)->toDateString();
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateString();
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();
        
        // Define the baccalaureate exam date globally for the countdown notifications 
        // Note: For real scenarios you may want this stored in a settings table, 
        // for now we set a representative date for the typical exam time.
        // Assuming June 8th of the current academic year.
        $examYear = date('n') >= 9 ? date('Y') + 1 : date('Y');
        $examDate = Carbon::createFromDate($examYear, 6, 8)->startOfDay();
        $daysUntilExam = Carbon::today()->startOfDay()->diffInDays($examDate, false);

        foreach ($activeNotifications as $notification) {
            $imageUrl = null;
            if (!empty($notification->image)) {
                $imageUrl = url(Storage::url($notification->image));
            }

            $usersQuery = User::whereNotNull('fcm_token');

            switch ($notification->trigger_type) {
                case 'daily_streak_reminder':
                    // User studied before but did NOT study today AND has an active streak (>0)
                    $usersQuery->whereNotNull('last_study_date')
                               ->where('current_streak', '>', 0)
                               ->whereDate('last_study_date', '<', $today);
                    break;
                    
                case 'streak_lost_1_day':
                    // User lost their streak. This means their last study date is exactly 2 days ago, 
                    // meaning they missed yesterday.
                    $usersQuery->whereNotNull('last_study_date')
                               ->whereDate('last_study_date', '=', $twoDaysAgo);
                    break;

                case 'inactive_1_day':
                    $usersQuery->whereDate('last_study_date', '=', $yesterday);
                    break;

                case 'inactive_3_days':
                    $usersQuery->whereDate('last_study_date', '=', $threeDaysAgo);
                    break;

                case 'inactive_7_days':
                    $usersQuery->whereDate('last_study_date', '=', $sevenDaysAgo);
                    break;
                    
                case 'inactive_14_days':
                    $usersQuery->whereDate('last_study_date', '=', $fourteenDaysAgo);
                    break;
                    
                case 'inactive_30_days':
                    $usersQuery->whereDate('last_study_date', '=', $thirtyDaysAgo);
                    break;
                    
                case 'exam_countdown_60':
                    if ($daysUntilExam !== 60) continue 2; // Skip entirely if it's not the day
                    break;
                    
                case 'exam_countdown_30':
                    if ($daysUntilExam !== 30) continue 2; // Skip entirely if it's not the day
                    break;
                    
                case 'exam_countdown_7':
                    if ($daysUntilExam !== 7) continue 2;  // Skip entirely if it's not the day
                    break;

                case 'subscription_guest_reminder':
                    // Send to users who do not have any active purchased subscriptions
                    $usersQuery->whereDoesntHave('subscription_cards', function ($q) {
                        $q->whereHas('subscription', function ($subQ) {
                            $subQ->where(function ($subQ2) {
                                $subQ2->whereNull('ending_date')
                                      ->orWhere('ending_date', '>', now());
                            })->where('id', '!=', \App\Models\Subscription::GUEST_ID);
                        });
                    });
                    break;

                case 'leaderboard_weekly_end':
                    // Check if today is Friday (end of week challenge)
                    // Carbon isFriday() returns true on Friday
                    if (!Carbon::today()->isFriday()) continue 2;
                    break;

                case 'study_weekend_reminder':
                    // Check if today is Saturday morning/weekend
                    if (!Carbon::today()->isSaturday()) continue 2;
                    break;

                case 'material_progress_0':
                case 'material_progress_10':
                case 'material_progress_50':
                case 'material_progress_100':
                    // These require processing user by user
                    $this->processMaterialProgressNotification($notification, $today, $imageUrl);
                    // Skip the default query-based sending for this trigger type
                    continue 2;

                default:
                    // If unknown trigger, skip
                    continue 2;
            }

            $usersToNotify = $usersQuery->get();

            if ($usersToNotify->isEmpty()) {
                $this->info("No users met condition for: {$notification->name}");
                continue;
            }

            $count = 0;
            foreach ($usersToNotify as $user) {
                $user->notify(new \App\Notifications\CustomUserNotification(
                    $notification->title, 
                    $notification->body, 
                    $imageUrl
                ));
                $count++;
            }

            $this->info("Sent '{$notification->name}' to {$count} users.");
        }

        $this->info('Automated notifications processed successfully.');
    }

    /**
     * Process notifications that depend on a user's material progress
     */
    private function processMaterialProgressNotification($notification, $today, $imageUrl)
    {
        $usersToNotify = User::whereNotNull('fcm_token')->with('division.materials')->get();
        $allMaterials = \App\Models\Material::pluck('name', 'id')->toArray();
        $count = 0;

        foreach ($usersToNotify as $user) {
            $progressData = $user->MaterialsProgress();
            if (empty($progressData)) continue;

            foreach ($progressData as $item) {
                $matId = $item['material_id'];
                $prog = $item['progress'];
                $matName = $allMaterials[$matId] ?? 'المادة';

                $shouldNotify = false;

                if ($notification->trigger_type == 'material_progress_0' && $prog == 0) {
                    // Send if it's been more than 7 days since account creation and they haven't started
                    if (Carbon::parse($user->created_at)->diffInDays(now()) >= 7) {
                        $shouldNotify = true;
                    }
                } elseif ($notification->trigger_type == 'material_progress_10' && $prog > 0 && $prog < 20) {
                    // Send if progress is between 1% and 20% and last studied exactly 3 days ago
                    $latestAnswer = \App\Models\UserAnswer::where('user_id', $user->id)
                        ->where('material_id', $matId)
                        ->max('created_at');
                    if ($latestAnswer && Carbon::parse($latestAnswer)->diffInDays(now()) == 3) {
                        $shouldNotify = true;
                    }
                } elseif ($notification->trigger_type == 'material_progress_50' && $prog >= 50 && $prog < 60) {
                    // Send if they just hit 50%+ today
                    $answeredToday = \App\Models\UserAnswer::where('user_id', $user->id)
                        ->where('material_id', $matId)
                        ->whereDate('created_at', $today)
                        ->exists();
                    if ($answeredToday) {
                        $shouldNotify = true;
                    }
                } elseif ($notification->trigger_type == 'material_progress_100' && $prog == 100) {
                    // Send if they hit 100% today
                    $answeredToday = \App\Models\UserAnswer::where('user_id', $user->id)
                        ->where('material_id', $matId)
                        ->whereDate('created_at', $today)
                        ->exists();
                    if ($answeredToday) {
                        $shouldNotify = true;
                    }
                }

                if ($shouldNotify) {
                    $title = str_replace('{material_name}', $matName, $notification->title);
                    $body = str_replace('{material_name}', $matName, $notification->body);

                    // Anti-spam: Check if we already sent this exact notification (for this material) 
                    // within the last 30 days
                    $alreadySent = \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('notifiable_type', User::class)
                        ->where('notifiable_id', $user->id)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->where('data', 'like', "%\"title\":\"{$title}\"%")
                        ->exists();

                    if (!$alreadySent) {
                        $user->notify(new \App\Notifications\CustomUserNotification($title, $body, $imageUrl));
                        $count++;
                        // Only notify for one material per rule daily to avoid bombing the user
                        break;
                    }
                }
            }
        }

        $this->info("Sent material progress '{$notification->name}' to {$count} users.");
    }
}
