<?php

namespace App\Exports;

use App\Models\Survey;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActiveSurveysExport implements FromCollection, WithHeadings
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $query = Survey::with(['courseOffering.course.department.faculty'])
            ->withCount('responses')
            ->where('is_active', true);

        if ($this->user->isUniversityAdmin()) {
            $query->where('scope_level', 'university')
                ->where('survey_owner', Survey::OWNER_QUALITY_CENTER);
        } elseif ($this->user->isPresidencyAdmin()) {
            $query->where('scope_level', 'university')
                ->where('survey_owner', Survey::OWNER_PRESIDENCY);
        } elseif ($this->user->isFacultyAdmin()) {
            $query->where('faculty_id', $this->user->faculty_id);
        } elseif ($this->user->isDepartmentAdmin()) {
            $query->where('department_id', $this->user->department_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->latest()->get()->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'survey_scope' => $survey->course_offering_id ? 'استبيان مادة' : 'استبيان عام',
                'scope_level' => match ($survey->scope_level) {
                    'university' => 'جامعة',
                    'faculty' => 'كلية',
                    'department' => 'قسم',
                    'course' => 'مقرر',
                    default => $survey->scope_level,
                },
                'faculty' => $survey->faculty?->name_ar ?? '—',
                'department' => $survey->department?->name_ar ?? $survey->department_name ?? '—',
                'course_title' => $survey->course_title ?? $survey->courseOffering?->course?->name_ar ?? '—',
                'academic_year' => $survey->academic_year ?? '—',
                'semester' => match ($survey->semester) {
                    'first' => 'الفصل الدراسي الأول',
                    'second' => 'الفصل الدراسي الثاني',
                    'summer' => 'الفصل الصيفي',
                    default => $survey->semester ?? '—',
                },
                'level' => $survey->level ?? '—',
                'responses_count' => $survey->responses_count,
                'link' => route('surveys.show', $survey->id),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'عنوان الاستبيان',
            'نوع الاستبيان',
            'النطاق',
            'الكلية',
            'القسم',
            'المقرر',
            'العام الدراسي',
            'الفصل الدراسي',
            'الفرقة',
            'عدد الردود',
            'رابط الاستبيان',
        ];
    }
}
