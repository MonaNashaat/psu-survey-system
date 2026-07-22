<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Support\Collection;

class SurveyAnalysisService
{
    /*
    |--------------------------------------------------------------------------
    | Analysis Settings
    |--------------------------------------------------------------------------
    |
    | satisfactionThreshold:
    | أقل قيمة تُعتبر إجابة راضية.
    |
    | acceptableAverage:
    | أقل متوسط يجعل نتيجة السؤال أو المحور مقبولة.
    |
    */

    private float $satisfactionThreshold = 4.0;

    private float $acceptableAverage = 2.2;

    /**
     * تحليل الاستبيان بالكامل.
     *
     * جميع الحسابات تتم داخل Laravel.
     * ملف Excel سيستقبل الردود والنتائج النهائية فقط.
     */
    public function analyze(Survey $survey): array
    {
        /*
         * تحميل جميع العلاقات مرة واحدة لتجنب تنفيذ Query
         * منفصل داخل كل سؤال أو كل استجابة.
         */
        $survey->loadMissing([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions.options',
            'responses.answers',
        ]);

        /*
         * ترتيب الردود حسب تاريخ الإرسال ثم ID.
         *
         * كل استجابة ستمثل عمودًا ثابتًا داخل ملف Excel:
         * Response 1
         * Response 2
         * ...
         */
        $responses = $survey->responses
            ->filter(fn ($response) => !is_null($response->submitted_at))
            ->sortBy(function ($response) {
                return sprintf(
                    '%s-%020d',
                    $response->submitted_at?->format('Y-m-d H:i:s.u')
                        ?? '9999-12-31 23:59:59.999999',
                    $response->id
                );
            })
            ->values();

        /*
         * خريطة اسم كل استجابة.
         *
         * مثال:
         * [
         *     15 => 'Response 1',
         *     19 => 'Response 2',
         * ]
         */
        $responseLabels = $responses->mapWithKeys(
            fn ($response, int $index) => [
                $response->id => 'Response ' . ($index + 1),
            ]
        );

        /*
         * تجميع جميع الإجابات حسب السؤال.
         *
         * بدلاً من البحث داخل كل الردود لكل سؤال.
         */
        $answersByQuestion = $responses
            ->flatMap(fn ($response) => $response->answers)
            ->groupBy('question_id');

        $sections = collect();

        $allNumericValues = collect();

        $allComments = collect();

        $questionSequence = 1;

        /*
         * تحليل أسئلة المحاور.
         */
        foreach ($survey->sections as $section) {
            $sectionValues = collect();

            $sectionQuestions = collect();

            foreach ($section->questions as $question) {
                $questionAnalysis = $this->analyzeQuestion(
                    question: $question,
                    answers: $answersByQuestion->get(
                        $question->id,
                        collect()
                    ),
                    responses: $responses,
                    responseLabels: $responseLabels,
                    sequence: $questionSequence,
                    sectionTitle: $section->title
                );

                $sectionQuestions->push($questionAnalysis);

                $sectionValues = $sectionValues->merge(
                    $questionAnalysis['numeric_values']
                );

                $allNumericValues = $allNumericValues->merge(
                    $questionAnalysis['numeric_values']
                );

                $allComments = $allComments->merge(
                    $questionAnalysis['comments']
                );

                $questionSequence++;
            }

            $sections->push([
                'id' => $section->id,

                'title' => $section->title,

                'display_order' => $section->display_order,

                'questions_count' => $sectionQuestions->count(),

                'questions' => $sectionQuestions->values()->all(),

                'statistics' => $this->calculateStatistics(
                    $sectionValues
                ),
            ]);
        }

        /*
         * بعض الاستبيانات قد تحتوي على أسئلة غير تابعة لمحور.
         * نضعها داخل محور افتراضي باسم "أسئلة إضافية".
         */
        $standaloneQuestions = $survey->questions
            ->whereNull('survey_section_id')
            ->sortBy('display_order')
            ->values();

        if ($standaloneQuestions->isNotEmpty()) {
            $standaloneValues = collect();

            $standaloneAnalysis = collect();

            foreach ($standaloneQuestions as $question) {
                $questionAnalysis = $this->analyzeQuestion(
                    question: $question,
                    answers: $answersByQuestion->get(
                        $question->id,
                        collect()
                    ),
                    responses: $responses,
                    responseLabels: $responseLabels,
                    sequence: $questionSequence,
                    sectionTitle: 'أسئلة إضافية'
                );

                $standaloneAnalysis->push($questionAnalysis);

                $standaloneValues = $standaloneValues->merge(
                    $questionAnalysis['numeric_values']
                );

                $allNumericValues = $allNumericValues->merge(
                    $questionAnalysis['numeric_values']
                );

                $allComments = $allComments->merge(
                    $questionAnalysis['comments']
                );

                $questionSequence++;
            }

            $sections->push([
                'id' => null,

                'title' => 'أسئلة إضافية',

                'display_order' => PHP_INT_MAX,

                'questions_count' => $standaloneAnalysis->count(),

                'questions' => $standaloneAnalysis->values()->all(),

                'statistics' => $this->calculateStatistics(
                    $standaloneValues
                ),
            ]);
        }

        return [
            /*
             * بيانات الاستبيان التي ستظهر أعلى ملف Excel.
             */
            'survey' => [
                'id' => $survey->id,

                'title' => $survey->title,

                'description' => $survey->description,

                'faculty' =>
                    $survey->faculty?->name_ar
                    ?? $survey->courseOffering
                        ?->course
                        ?->department
                        ?->faculty
                        ?->name_ar,

                'department' =>
                    $survey->department?->name_ar
                    ?? $survey->department_name
                    ?? $survey->courseOffering
                        ?->course
                        ?->department
                        ?->name_ar,

                'course' =>
                    $survey->courseOffering?->course?->name_ar
                    ?? $survey->course_title,

                'semester' => $survey->semester,

                'level' => $survey->level,

                'academic_year' => $survey->academic_year,
            ],

            /*
             * المعايير المستخدمة في الحساب.
             */
            'settings' => [
                'satisfaction_threshold' =>
                    $this->satisfactionThreshold,

                'acceptable_average' =>
                    $this->acceptableAverage,
            ],

            /*
             * قائمة الردود بالترتيب الذي ستظهر به في Excel.
             */
            'responses' => $responses
                ->map(fn ($response, int $index) => [
                    'id' => $response->id,

                    'label' => 'Response ' . ($index + 1),

                    'submitted_at' =>
                        $response->submitted_at
                            ?->format('Y-m-d H:i:s'),
                ])
                ->values()
                ->all(),

            'total_responses' => $responses->count(),

            /*
             * المحاور والأسئلة والردود والإحصائيات.
             */
            'sections' => $sections->values()->all(),

            /*
             * النتيجة الإجمالية لكل الإجابات الرقمية.
             */
            'overall_statistics' =>
                $this->calculateStatistics($allNumericValues),

            /*
             * جميع التعليقات النصية.
             */
            'comments' => $allComments->values()->all(),
        ];
    }

    /**
     * تحليل سؤال واحد.
     */
    private function analyzeQuestion(
        Question $question,
        Collection $answers,
        Collection $responses,
        Collection $responseLabels,
        int $sequence,
        string $sectionTitle
    ): array {
        /*
         * وضع إجابة كل Response مقابل رقم الاستجابة.
         */
        $answersByResponse = $answers->keyBy(
            'survey_response_id'
        );

        /*
         * الأسئلة الرقمية التي تدخل في المتوسطات.
         */
        $isNumeric = in_array(
            $question->type,
            ['scale', 'mcq'],
            true
        );

        /*
         * تجهيز الردود الفردية بنفس ترتيب المشاركين.
         *
         * لو Response لم يجب عن السؤال، ستكون القيمة null،
         * وبالتالي ستظهر الخلية فارغة داخل Excel.
         */
        $individualResponses = $responses
            ->map(function ($response) use (
                $answersByResponse,
                $responseLabels,
                $isNumeric
            ) {
                $answer = $answersByResponse->get(
                    $response->id
                );

                return [
                    'response_id' => $response->id,

                    'label' => $responseLabels->get(
                        $response->id
                    ),

                    'value' =>
                        $isNumeric
                        && is_numeric($answer?->answer_value)
                            ? (float) $answer->answer_value
                            : null,

                    'text' =>
                        !$isNumeric
                            ? $answer?->answer_text
                            : null,

                    'question_option_id' =>
                        $answer?->question_option_id,
                ];
            })
            ->values();

        /*
         * القيم الرقمية المستخدمة في الحساب.
         */
        $numericValues = $individualResponses
            ->pluck('value')
            ->filter(fn ($value) => !is_null($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        /*
         * حساب توزيع الاختيارات.
         *
         * مثال:
         * Strongly Agree = 20
         * Agree = 35
         * Neutral = 10
         */
        $distribution = $question->options
            ->map(function ($option) use ($answers) {
                $count = $answers
                    ->where(
                        'question_option_id',
                        $option->id
                    )
                    ->count();

                return [
                    'option_id' => $option->id,

                    'label' => $option->option_text,

                    'value' => is_numeric(
                        $option->option_value
                    )
                        ? (float) $option->option_value
                        : $option->option_value,

                    'count' => $count,
                ];
            })
            ->values();

        /*
         * استخراج الإجابات النصية.
         */
        $comments = $individualResponses
            ->filter(
                fn (array $item) => filled($item['text'])
            )
            ->map(fn (array $item) => [
                'section' => $sectionTitle,

                'question_number' => $sequence,

                'question' => $question->question_text,

                'response_id' => $item['response_id'],

                'response_label' => $item['label'],

                'comment' => $item['text'],
            ])
            ->values();

        return [
            'id' => $question->id,

            'number' => $sequence,

            'code' => 'Q' . $sequence,

            'section' => $sectionTitle,

            'text' => $question->question_text,

            'type' => $question->type,

            'is_numeric' => $isNumeric,

            'is_required' => (bool) $question->is_required,

            'display_order' => $question->display_order,

            /*
             * كل رد في عمود مستقل داخل Excel.
             */
            'individual_responses' =>
                $individualResponses->all(),

            /*
             * هذه القيم للاستخدام الداخلي في حساب المحور
             * والنتيجة الإجمالية.
             */
            'numeric_values' => $numericValues->all(),

            /*
             * النتائج التي حسبها Laravel.
             */
            'statistics' => $isNumeric
                ? $this->calculateStatistics($numericValues)
                : $this->emptyStatistics(
                    $comments->count()
                ),

            /*
             * عدد مرات اختيار كل اختيار.
             */
            'distribution' => $distribution->all(),

            /*
             * التعليقات الخاصة بالسؤال.
             */
            'comments' => $comments->all(),
        ];
    }

    /**
     * حساب الإحصائيات الرقمية.
     */
    private function calculateStatistics(
        Collection $values
    ): array {
        $values = $values
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        $count = $values->count();

        if ($count === 0) {
            return $this->emptyStatistics();
        }

        $average = (float) $values->avg();

        $satisfiedCount = $values
            ->filter(
                fn (float $value) =>
                    $value >= $this->satisfactionThreshold
            )
            ->count();

        return [
            /*
             * عدد الطلاب الذين أجابوا عن السؤال.
             */
            'count' => $count,

            /*
             * مجموع القيم.
             */
            'sum' => round(
                (float) $values->sum(),
                2
            ),

            /*
             * المتوسط.
             */
            'average' => round(
                $average,
                2
            ),

            /*
             * أقل وأعلى إجابة.
             */
            'minimum' => round(
                (float) $values->min(),
                2
            ),

            'maximum' => round(
                (float) $values->max(),
                2
            ),

            /*
             * عدد الطلاب الذين حققوا حد الرضا.
             */
            'satisfied_count' => $satisfiedCount,

            /*
             * نسبة الرضا.
             */
            'satisfaction_percentage' => round(
                ($satisfiedCount / $count) * 100,
                2
            ),

            /*
             * هل المتوسط وصل إلى حد القبول؟
             */
            'acceptable' =>
                $average >= $this->acceptableAverage,
        ];
    }

    /**
     * القيم الافتراضية في حالة عدم وجود إجابات.
     */
    private function emptyStatistics(
        int $count = 0
    ): array {
        return [
            'count' => $count,

            'sum' => null,

            'average' => null,

            'minimum' => null,

            'maximum' => null,

            'satisfied_count' => 0,

            'satisfaction_percentage' => 0.0,

            'acceptable' => false,
        ];
    }
}