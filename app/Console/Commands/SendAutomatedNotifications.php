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
        $threeDaysAgo = Carbon::now()->subDays(3)->toDateString();
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        foreach ($activeNotifications as $notification) {
            $imageUrl = null;
            if (!empty($notification->image)) {
                $imageUrl = url(Storage::url($notification->image));
            }

            $usersQuery = User::whereNotNull('fcm_token');

            switch ($notification->trigger_type) {
                case 'daily_streak_reminder':
                    // User studied before but did NOT study today AND has an active streak
                    // We remind them to not lose their streak
                    $usersQuery->whereNotNull('last_study_date')
                               ->whereDate('last_study_date', '<', $today);
                    break;

                case 'inactive_1_day':
                    // Exactly yesterday
                    $usersQuery->whereDate('last_study_date', '=', $yesterday);
                    break;

                case 'inactive_3_days':
                    // Exactly 3 days ago
                    $usersQuery->whereDate('last_study_date', '=', $threeDaysAgo);
                    break;

                case 'inactive_7_days':
                    // Exactly 7 days ago
                    $usersQuery->whereDate('last_study_date', '=', $sevenDaysAgo);
                    break;

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
}
