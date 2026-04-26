<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoursesImport implements ToCollection, WithHeadingRow
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $departmentId = $row['department_id'] ?? null;
            $departmentNameAr = trim((string) ($row['department_name_ar'] ?? ''));
            $code = trim((string) ($row['code'] ?? ''));
            $nameAr = trim((string) ($row['name_ar'] ?? ''));
            $nameEn = trim((string) ($row['name_en'] ?? ''));

            if (!$nameAr) {
                continue;
            }

            $department = null;

            if ($departmentId) {
                $department = Department::with('faculty')->find($departmentId);
            } elseif ($departmentNameAr !== '') {
                $department = Department::with('faculty')
                    ->where('name_ar', $departmentNameAr)
                    ->first();
            }

            if (!$department) {
                throw new \Exception('القسم غير موجود في الصف رقم ' . ($index + 2));
            }

            if ($this->user->role === 'faculty_admin') {
                if ((int) $department->faculty_id !== (int) $this->user->faculty_id) {
                    throw new \Exception('يوجد مقرر خارج نطاق كلية المستخدم في الصف رقم ' . ($index + 2));
                }
            }

            if ($this->user->role === 'department_admin') {
                if ((int) $department->id !== (int) $this->user->department_id) {
                    throw new \Exception('يوجد مقرر خارج نطاق قسم المستخدم في الصف رقم ' . ($index + 2));
                }
            }

            Course::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'code' => $code ?: null,
                    'name_ar' => $nameAr,
                ],
                [
                    'name_en' => $nameEn ?: null,
                ]
            );
        }
    }
}