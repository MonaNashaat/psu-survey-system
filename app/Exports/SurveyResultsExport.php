<?php

namespace App\Exports;

use App\Models\Survey;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SurveyResultsExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithEvents,
    ShouldAutoSize
{
    protected Survey $survey;
    protected Collection $questions;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey->load([
            'sections.questions.options',
            'questions.options',
            'responses.answers.option',
        ]);

        /*
        |--------------------------------------------------------------------------
        | جميع أسئلة الاستبيان بالترتيب
        |--------------------------------------------------------------------------
        */

        $sectionQuestions = $this->survey->sections
            ->sortBy('display_order')
            ->flatMap(function ($section) {
                return $section->questions
                    ->sortBy('display_order');
            });

        $standaloneQuestions = $this->survey->questions
            ->whereNull('survey_section_id')
            ->sortBy('display_order');

        $this->questions = $sectionQuestions
            ->concat($standaloneQuestions)
            ->unique('id')
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | عناوين الأعمدة
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        $headings = [
            'م',
            'تاريخ الاستجابة',
        ];

        foreach ($this->questions as $index => $question) {
            $headings[] =
                'س' . ($index + 1) . ' - ' . $question->question_text;
        }

        return $headings;
    }

    /*
    |--------------------------------------------------------------------------
    | الاستجابات
    |--------------------------------------------------------------------------
    */

    public function collection()
    {
        $rows = [];

        foreach (
            $this->survey->responses
                ->sortBy('created_at')
                ->values()
            as $responseIndex => $response
        ) {

            $row = [
                $responseIndex + 1,

                $response->created_at
                    ? $response->created_at->format('Y-m-d H:i')
                    : '',
            ];

            /*
             * نجمع Answers الخاصة بهذه الاستجابة حسب السؤال.
             *
             * هذا مهم خصوصًا للـ checkbox لأن السؤال الواحد
             * يمكن أن يحتوي على أكثر من Answer.
             */
            $answersByQuestion = $response->answers
                ->groupBy('question_id');

            foreach ($this->questions as $question) {

                $answers = $answersByQuestion
                    ->get($question->id, collect());

                $row[] = $this->formatAnswer(
                    $question,
                    $answers
                );
            }

            $rows[] = $row;
        }

        return collect($rows);
    }

    /*
    |--------------------------------------------------------------------------
    | تحويل الإجابة إلى نص مناسب للـ Excel
    |--------------------------------------------------------------------------
    */

    private function formatAnswer($question, Collection $answers): string
    {
        if ($answers->isEmpty()) {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Checkbox
        |--------------------------------------------------------------------------
        |
        | أكثر من اختيار لنفس السؤال.
        |
        */

        if ($question->type === 'checkbox') {

            return $answers
                ->map(function ($answer) {

                    if ($answer->option) {
                        return $answer->option->option_text;
                    }

                    if (filled($answer->answer_text)) {
                        return $answer->answer_text;
                    }

                    if ($answer->answer_value !== null) {
                        return (string) $answer->answer_value;
                    }

                    return null;
                })
                ->filter()
                ->unique()
                ->implode('، ');
        }

        /*
        |--------------------------------------------------------------------------
        | MCQ
        |--------------------------------------------------------------------------
        */

        if ($question->type === 'mcq') {

            $answer = $answers->first();

            if ($answer?->option) {
                return $answer->option->option_text;
            }

            return filled($answer?->answer_text)
                ? $answer->answer_text
                : '';
        }

        /*
        |--------------------------------------------------------------------------
        | Scale
        |--------------------------------------------------------------------------
        */

        if ($question->type === 'scale') {

            $answer = $answers->first();

            /*
             * الأفضل عرض نص الاختيار للمستخدم
             * مثل: موافق بشدة
             * بدلًا من الرقم فقط.
             */
            if ($answer?->option) {
                return $answer->option->option_text;
            }

            if ($answer?->answer_value !== null) {
                return (string) $answer->answer_value;
            }

            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Text / Short Text / Date
        |--------------------------------------------------------------------------
        */

        if (in_array(
            $question->type,
            ['text', 'short_text', 'date'],
            true
        )) {

            return $answers
                ->pluck('answer_text')
                ->filter(fn ($value) => filled($value))
                ->implode('، ');
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        $answer = $answers->first();

        if ($answer?->option) {
            return $answer->option->option_text;
        }

        if (filled($answer?->answer_text)) {
            return $answer->answer_text;
        }

        if ($answer?->answer_value !== null) {
            return (string) $answer->answer_value;
        }

        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | تنسيق Header
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | إعدادات Sheet
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                 * اتجاه الصفحة من اليمين لليسار
                 */
                $sheet->setRightToLeft(true);

                /*
                 * تثبيت أول صف
                 */
                $sheet->freezePane('A2');

                /*
                 * Auto Filter
                 */
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                if ($highestRow >= 1) {
                    $sheet->setAutoFilter(
                        "A1:{$highestColumn}{$highestRow}"
                    );
                }

                /*
                 * Wrap Text لكل البيانات
                 */
                $sheet->getStyle(
                    "A1:{$highestColumn}{$highestRow}"
                )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_TOP
                    )
                    ->setWrapText(true);

                /*
                 * ارتفاع Header
                 */
                $sheet->getRowDimension(1)
                    ->setRowHeight(45);

                /*
                 * عرض عمود الرقم
                 */
                $sheet->getColumnDimension('A')
                    ->setWidth(8);

                /*
                 * عرض التاريخ
                 */
                $sheet->getColumnDimension('B')
                    ->setWidth(22);

                /*
                 * محاذاة الرقم والتاريخ
                 */
                if ($highestRow > 1) {

                    $sheet->getStyle(
                        "A2:B{$highestRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );
                }
            },
        ];
    }
}