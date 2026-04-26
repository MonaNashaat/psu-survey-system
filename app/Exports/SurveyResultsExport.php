<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SurveyResultsExport implements FromArray, WithHeadings
{
    protected Survey $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey->load([
            'sections.questions.options',
            'questions.options',
            'responses.answers.option',
        ]);
    }

    public function headings(): array
    {
        return [
            'المحور',
            'رقم السؤال',
            'نص السؤال',
            'نوع السؤال',
            'عدد الإجابات',
            'المتوسط',
            'الاختيار',
            'عدد مرات الاختيار',
            'التعليقات',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->survey->sections as $section) {
            foreach ($section->questions as $question) {
                $answers = $this->survey->responses
                    ->flatMap(fn ($response) => $response->answers)
                    ->where('question_id', $question->id);

                if (in_array($question->type, ['scale', 'mcq'])) {
                    $average = $answers->whereNotNull('answer_value')->avg('answer_value');

                    foreach ($question->options as $option) {
                        $count = $answers->where('question_option_id', $option->id)->count();

                        $rows[] = [
                            $section->title,
                            $question->display_order,
                            $question->question_text,
                            $question->type,
                            $answers->count(),
                            $average !== null ? round($average, 2) : null,
                            $option->option_text,
                            $count,
                            null,
                        ];
                    }
                } else {
                    $comments = $answers->pluck('answer_text')->filter()->implode(' | ');

                    $rows[] = [
                        $section->title,
                        $question->display_order,
                        $question->question_text,
                        $question->type,
                        $answers->whereNotNull('answer_text')->count(),
                        null,
                        null,
                        null,
                        $comments,
                    ];
                }
            }
        }

        $standaloneQuestions = $this->survey->questions->whereNull('survey_section_id');

        foreach ($standaloneQuestions as $question) {
            $answers = $this->survey->responses
                ->flatMap(fn ($response) => $response->answers)
                ->where('question_id', $question->id);

            $comments = $answers->pluck('answer_text')->filter()->implode(' | ');

            $rows[] = [
                'أسئلة إضافية',
                $question->display_order,
                $question->question_text,
                $question->type,
                $answers->whereNotNull('answer_text')->count(),
                null,
                null,
                null,
                $comments,
            ];
        }

        return $rows;
    }
}