<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\ChallengeProgress;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Http\Request;

class ChallengeController extends BaseController
{
    /**
     * Get questions for the challenge based on user level in this unit.
     */
    public function getQuestions(Request $request, $unit_id)
    {
        try {
            $user = $request->user();

            // 1. Get or create progress
            $progress = ChallengeProgress::firstOrCreate(
                ['user_id' => $user->id, 'unit_id' => $unit_id],
                ['level' => 1, 'points' => 0, 'games_played' => 0, 'games_won' => 0]
            );

            $unit = Unit::findOrFail($unit_id);
            
            // 2. Determine chapters pool based on level
            $chapters = $unit->chapters()->orderBy('chapter_unit.sort')->get();
            if ($chapters->isEmpty()) {
                return $this->sendError('لا توجد فصول في هذا المحور بعد.', [], 404);
            }

            // Logic: level 1 uses 1 chapter, level 2 uses 2 chapters...
            $numChapters = min($progress->level, $chapters->count());
            $targetChapterIds = $chapters->take($numChapters)->pluck('id');

            // 3. Fetch suitable questions
            $questions = Question::whereHas('chapters', function ($query) use ($targetChapterIds) {
                $query->whereIn('chapters.id', $targetChapterIds);
            })
            ->whereIn('question_type', ['multiple_choices', 'true_or_false']) // fast-paced
            ->inRandomOrder()
            ->take(12)
            ->get();

            if ($questions->isEmpty()) {
                return $this->sendError('لا توجد أسئلة كافية للتحدي حالياً.', [], 404);
            }

            // Standardize output
            $questions->each(function($q) {
                try { $q->append('image'); } catch(\Exception $e) {}
                try { $q->append('audio'); } catch(\Exception $e) {}
            });

            return $this->sendResponse([
                'unit' => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ],
                'progress' => [
                    'level' => $progress->level,
                    'points' => $progress->points,
                ],
                'questions' => $questions,
            ], 'تم جلب الأسئلة بنجاح');
        } catch (\Exception $e) {
            return $this->sendError('Backend Error: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Submit challenge results.
     */
    public function submitResult(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'is_winner' => 'required|boolean',
            'points_gained' => 'required|integer|min:0',
        ]);

        try {
            $user = $request->user();
            $unit_id = $request->unit_id;
            $isWinner = $request->is_winner;
            $pointsGained = $request->points_gained;

            $progress = ChallengeProgress::firstOrCreate(
                ['user_id' => $user->id, 'unit_id' => $unit_id],
                ['level' => 1, 'points' => 0, 'games_played' => 0, 'games_won' => 0]
            );

            $progress->games_played += 1;
            if ($isWinner) {
                $progress->games_won += 1;
            }

            $progress->points += $pointsGained;

            // Level Up Logic: Level = floor(wins / 3) + 1
            $progress->level = max(1, floor($progress->games_won / 3) + 1);
            $progress->save();

            return $this->sendResponse([
                'progress' => [
                    'level' => $progress->level,
                    'points' => $progress->points,
                    'games_played' => $progress->games_played,
                    'games_won' => $progress->games_won,
                ]
            ], 'تم حفظ النتيجة بنجاح');
        } catch (\Exception $e) {
            return $this->sendError('Server Error Details: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), [], 500);
        }
    }
}
