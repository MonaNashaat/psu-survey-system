<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CourseOfferingsImport implements ToCollection, WithHeadingRow
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $courseCode = trim((string) ($row['course_code'] ?? ''));
            $academicYear = trim((string) ($row['academic_year'] ?? ''));
            $semester = strtolower(trim((string) ($row['semester'] ?? '')));
            $level = trim((string) ($row['level'] ?? ''));
            $instructorName = trim((string) ($row['instructor_name'] ?? ''));
            $assistantName = trim((string) ($row['assistant_name'] ?? ''));

            if ($courseCode === '' && $academicYear === '' && $semester === '' && $level === '') {
                continue;
            }

            if ($courseCode === '' || $academicYear === '' || $semester === '' || $level === '') {
                throw new \Exception('هناك بيانات ناقصة في الصف رقم ' . ($index + 2));
            }

            if (!in_array($semester, ['first', 'second', 'summer'], true)) {
                throw new \Exception('قيمة semester غير صحيحة في الصف رقم ' . ($index + 2) . '. القيم المسموحة: first, second, summer');
            }

            $courseQuery = Course::with('department.faculty')
                ->where('code', $courseCode);

            if ($this->user->isFacultyAdmin()) {
                $courseQuery->whereHas('department', function ($query) {
                    $query->where('faculty_id', $this->user->faculty_id);
                });
            }

            if ($this->user->isDepartmentAdmin()) {
                $courseQuery->where('department_id', $this->user->department_id);
            }

            $course = $courseQuery->first();

            if (!$course) {
                throw new \Exception(
                    'لم يتم العثور على مقرر بالكود ' . $courseCode .
                    ' داخل نطاق صلاحية المستخدم في الصف رقم ' . ($index + 2)
                );
            }

            CourseOffering::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                    'level' => $level,
                ],
                [
                    'instructor_name' => $instructorName !== '' ? $instructorName : null,
                    'assistant_name' => $assistantName !== '' ? $assistantName : null,
                ]
            );
        }
    }
}