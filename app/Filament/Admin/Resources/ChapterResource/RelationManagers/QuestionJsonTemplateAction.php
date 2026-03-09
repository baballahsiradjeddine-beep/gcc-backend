<?php

namespace App\Filament\Admin\Resources\ChapterResource\RelationManagers;

use App\Enums\ContentDirection;
use App\Enums\QuestionScope;
use App\Enums\QuestionType;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Response;

class QuestionJsonTemplateAction
{
    public static function make(): Action
    {
        return Action::make('download_real_json')
            ->label('تصدير الأسئلة (JSON)')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function ($livewire) {
                /** @var \App\Models\Chapter $chapter */
                $chapter = $livewire->getOwnerRecord();
                $chapterName = $chapter->name;
                
                $unit = $chapter->unit()->first();
                $materialName = $unit?->material()?->first()?->name ?? 'Unknown';

                $filename = \Illuminate\Support\Str::slug("Questions_{$materialName}_{$chapterName}") . '.json';

                // Fetch real questions from the database
                $questions = $chapter->questions;

                $data = [];
                foreach ($questions as $q) {
                    $data[] = [
                        'question' => $q->question,
                        'question_type' => $q->question_type->value,
                        'scope' => $q->scope->value,
                        'direction' => $q->direction->value,
                        'question_is_latex' => (bool)$q->question_is_latex,
                        'explanation_text' => $q->explanation_text,
                        'explanation_text_is_latex' => (bool)$q->explanation_text_is_latex,
                        'hint' => $q->hint,
                        'options' => $q->options,
                    ];
                }

                // If no questions, provide one correctly structured row as a baseline
                if (empty($data)) {
                    $data = [
                        [
                            'question' => "أدخل نص السؤال هنا لـ {$chapterName}",
                            'question_type' => QuestionType::MULTIPLE_CHOICES->value,
                            'scope' => QuestionScope::EXERCICE->value,
                            'direction' => ContentDirection::INHERIT->value,
                            'question_is_latex' => false,
                            'explanation_text' => '',
                            'explanation_text_is_latex' => false,
                            'hint' => [],
                            'options' => [
                                'choices' => [
                                    ['option' => 'الخيار 1', 'is_correct' => true, 'option_is_latex' => false],
                                    ['option' => 'الخيار 2', 'is_correct' => false, 'option_is_latex' => false],
                                ]
                            ]
                        ]
                    ];
                }

                $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                return Response::streamDownload(function () use ($json) {
                    echo $json;
                }, $filename);
            });
    }
}
