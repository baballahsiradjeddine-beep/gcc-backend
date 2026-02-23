<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Dedoc\Scramble\Attributes\Group;

#[Group('Gamification (Streaks) APIs', weight: 4)]
class StreakControllerV2 extends BaseController
{
    /**
     * Get User Streak Info.
     *
     * This endpoint returns the user's current streak, longest streak,
     * and a boolean array representing their study activity for the past 7 days.
     */
    public function getStreak(Request $request)
    {
        $user = $request->user();
        
        // Ensure streaks are recalculated if the user missed days
        $this->validateAndResetStreak($user);

        return $this->sendResponse($this->getStreakPayload($user));
    }

    /**
     * Increment/Ping Daily Streak.
     *
     * Call this endpoint when the user completes a study session (flashcards, quiz, summary).
     * It marks today as a study day, increments the streak, and returns the updated status.
     */
    public function pingStreak(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // Check if already studied today
        $alreadyLogged = DB::table('user_study_logs')
            ->where('user_id', $user->id)
            ->whereDate('study_date', $today->format('Y-m-d'))
            ->exists();

        $didIncreaseStreak = false;

        if (!$alreadyLogged) {
            // Log today's study
            DB::table('user_study_logs')->insert([
                'user_id' => $user->id,
                'study_date' => $today->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->validateAndResetStreak($user);

            $lastStudyDate = $user->last_study_date ? Carbon::parse($user->last_study_date)->startOfDay() : null;

            if ($lastStudyDate && $lastStudyDate->isSameDay($today->copy()->subDay())) {
                // Studied yesterday, increment streak
                $user->current_streak += 1;
            } elseif (!$lastStudyDate || $lastStudyDate->isBefore($today)) {
                // Streak broken or just starting
                $user->current_streak = 1;
            }
            
            // Update longest streak if necessary
            if ($user->current_streak > $user->longest_streak) {
                $user->longest_streak = $user->current_streak;
            }

            $user->last_study_date = $today->format('Y-m-d');
            $user->save();
            
            $didIncreaseStreak = true;
        }

        $payload = array_merge(
            $this->getStreakPayload($user),
            ['streak_increased_today' => $didIncreaseStreak]
        );

        return $this->sendResponse($payload);
    }

    /**
     * Helper to validate and reset the user's current streak if they missed a day.
     */
    private function validateAndResetStreak($user)
    {
        if ($user->current_streak > 0 && $user->last_study_date) {
            $lastStudyDate = Carbon::parse($user->last_study_date)->startOfDay();
            $yesterday = Carbon::yesterday();

            // If the last study date is before yesterday, the streak is broken
            if ($lastStudyDate->isBefore($yesterday)) {
                $user->current_streak = 0;
                $user->save();
            }
        }
    }

    /**
     * Helper to get the weekly activity boolean array for the UI checkboxes.
     */
    private function getStreakPayload($user)
    {
        // For the UI, we'll return the last 7 days ending with today.
        // E.g. [ { date: 'Sun', studied: true }, ... ]
        $history = [];
        $logs = DB::table('user_study_logs')
            ->where('user_id', $user->id)
            ->whereDate('study_date', '>=', Carbon::today()->subDays(6)->format('Y-m-d'))
            ->pluck('study_date')
            ->toArray();

        // In Algeria, the week often starts on Sunday, but for streaks,
        // it's usually better to just show the last 7 days relative to today.
        // Let's provide an array of the last 7 days up to today.
        $recentDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $recentDays[] = [
                'date' => $dateString,
                'day_name' => $this->getArabicDayName($date->dayOfWeek),
                'studied' => in_array($dateString, $logs),
                'is_today' => $i === 0
            ];
        }

        return [
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'history' => $recentDays,
        ];
    }

    /**
     * Map day of week to Arabic name.
     */
    private function getArabicDayName($dayOfWeek)
    {
        $days = [
            0 => 'الأحد',
            1 => 'الإثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];

        return $days[$dayOfWeek] ?? '';
    }
}
