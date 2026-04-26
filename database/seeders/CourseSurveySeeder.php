<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Models\SurveySection;
use Illuminate\Database\Seeder;

class CourseSurveySeeder extends Seeder
{
    public function run(): void
    {
        $survey = Survey::create([
            'title' => 'إستمارة إستطلاع رأي حول مقرر دراسي',
            'description' => 'يرجى الإجابة على جميع البنود بكل دقة وموضوعية.',
            'course_title' => 'مقرر تجريبي',
            'department_name' => 'قسم تجريبي',
            'semester' => 'الفصل الدراسي الأول',
            'level' => 'الفرقة الأولى',
            'academic_year' => '2024-2025',
            'is_active' => true,
        ]);

        $scaleOptions = [
            ['غير موافق بشدة', 1],
            ['غير موافق', 2],
            ['محايد', 3],
            ['أوافق', 4],
            ['أوافق بشدة', 5],
        ];

        $addScaleQuestion = function ($section, $text, $order) use ($survey, $scaleOptions) {
            $question = Question::create([
                'survey_id' => $survey->id,
                'survey_section_id' => $section->id,
                'question_text' => $text,
                'type' => 'scale',
                'is_required' => true,
                'display_order' => $order,
            ]);

            foreach ($scaleOptions as $index => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option[0],
                    'option_value' => $option[1],
                    'display_order' => $index + 1,
                ]);
            }
        };

        $courseSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'المقرر الدراسي',
            'display_order' => 1,
        ]);

        $learningOutcomesSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'مخرجات التعلم المستهدفة',
            'display_order' => 2,
        ]);

        $lecturesSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'المحاضرات',
            'display_order' => 3,
        ]);

        $lecturerSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'المحاضر',
            'display_order' => 4,
        ]);

        $assistantSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'عضو الهيئة المعاونة',
            'display_order' => 5,
        ]);

        $assessmentSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'نظام التقويم',
            'display_order' => 6,
        ]);

        $facilitiesSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'المعامل / الورش / المدرجات / قاعات التدريس',
            'display_order' => 7,
        ]);

        $precautionSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'الإجراءات الإحترازية',
            'display_order' => 8,
        ]);

        $addScaleQuestion($courseSection, 'المقرر الدراسي شامل ووافي وتم عرضه بطريقة شيقة وغير مملة', 1);
        $addScaleQuestion($courseSection, 'المقرر يرتبط بالتخصص ويتضمن معلومات حديثة ويوفر أمثلة عملية ومفيدة', 2);

        $addScaleQuestion($learningOutcomesSection, 'المقرر له أهداف واضحة ويزودني بالمعرفة المفيدة والفهم المتعمق للموضوع', 3);
        $addScaleQuestion($learningOutcomesSection, 'المقرر يحفزني على التفكير ويكسبني المهارات المهنية التي تفيد في الحياة العملية', 4);

        $addScaleQuestion($lecturesSection, 'يتم تقديم المحاضرات وفقا لمواعيد الجداول المحددة والمعلنة', 5);
        $addScaleQuestion($lecturesSection, 'مقدار المعلومات المقدمة في المحاضرات مناسب', 6);
        $addScaleQuestion($lecturesSection, 'كتاب المقرر (أو CD) يعتبر مناسباً', 7);

        $addScaleQuestion($lecturerSection, 'يلتزم دائما المحاضر بمحتويات المقرر', 8);
        $addScaleQuestion($lecturerSection, 'يلتزم دائما المحاضر بمواعيد بدء وإنتهاء المحاضرات وفقاً للجداول المعلنة', 9);
        $addScaleQuestion($lecturerSection, 'يشجع المحاضر الطلاب على الأسئلة والتعبير عن وجهة نظرهم', 10);
        $addScaleQuestion($lecturerSection, 'يستثمر المحاضر وقت المحاضرة في التدريس الفعلي ويقدم أمثلة وحالات عملية فعالة', 11);

        $addScaleQuestion($assistantSection, 'عضو الهيئة المعاونة دائمًا على استعداد للرد على أي استفسارات', 12);
        $addScaleQuestion($assistantSection, 'يوفر عضو الهيئة المعاونة لنا التطبيقات الكافية وملم بموضوعات المقرر', 13);

        $addScaleQuestion($assessmentSection, 'يعتبر جدول الامتحانات مناسبًا ويتم الإعلان عن موعدها مبكراً', 14);
        $addScaleQuestion($assessmentSection, 'الوقت المخصص للامتحانات مناسب وتغطي الامتحانات محتويات المقرر', 15);
        $addScaleQuestion($assessmentSection, 'تركز الامتحانات على الجوانب النظرية والعملية في المقرر', 16);
        $addScaleQuestion($assessmentSection, 'يتصف توزيع الدرجات المقررة بالعدالة', 17);

        $addScaleQuestion($facilitiesSection, 'يتوفر بالكلية معامل وورش كافية لتحقيق أهداف العملية التعليمية', 18);
        $addScaleQuestion($facilitiesSection, 'يوجد بالمعامل والورش الأجهزة والمعدات الحديثة', 19);
        $addScaleQuestion($facilitiesSection, 'تسهيلات التدريس المتاحة في القاعات ملائمة', 20);
        $addScaleQuestion($facilitiesSection, 'هل مساحة المكان مناسبة وعدد المقاعد / البنشات كافٍ', 21);

        $addScaleQuestion($precautionSection, 'نسبة الرضا عن الإجراءات الإحترازية التي اتخذتها الكلية خلال فترة العام الدراسي', 22);
        $addScaleQuestion($precautionSection, 'مدى التزامك بالإجراءات الإحترازية', 23);

        Question::create([
            'survey_id' => $survey->id,
            'survey_section_id' => null,
            'question_text' => 'تعليقات أخرى',
            'type' => 'text',
            'is_required' => false,
            'display_order' => 24,
        ]);
    }
}