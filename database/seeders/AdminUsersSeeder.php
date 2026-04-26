<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Survey;
use App\Models\SurveyPermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = Faculty::where('name_ar', 'كلية الهندسة')->first()
            ?? Faculty::orderBy('id')->first();

        if (!$faculty) {
            return;
        }

        // 1) أدمن الجامعة
        $universityAdmin = User::updateOrCreate(
            ['email' => 'qac@psu.edu.eg'],
            [
                'name' => 'University Admin',
                'password' => Hash::make('123456'),
                'role' => 'university_admin',
                'faculty_id' => null,
                'department_id' => null,
            ]
        );

        // 2) أدمن الكلية
        $facultyAdmin = User::updateOrCreate(
            ['email' => 'qac@eng.psu.edu.eg'],
            [
                'name' => 'Faculty Admin',
                'password' => Hash::make('123456'),
                'role' => 'faculty_admin',
                'faculty_id' => $faculty->id,
                'department_id' => null,
            ]
        );

        
        // 3) مستخدم عادي لعرض نتائج استبيان محدد فقط
        $resultsViewer = User::updateOrCreate(
            ['email' => 'viewer@psu.edu.eg'],
            [
                'name' => 'Results Viewer',
                'password' => Hash::make('123456'),
                'role' => 'results_viewer',
                'faculty_id' => null,
                'department_id' => null,
            ]
        );

        $survey = Survey::orderBy('id')->first();

        if ($survey) {
            SurveyPermission::updateOrCreate(
                [
                    'survey_id' => $survey->id,
                    'user_id' => $resultsViewer->id,
                    'permission_type' => 'view_results',
                ],
                []
            );
        }

        // 5) أدمنات أقسام كلية الهندسة
        $engineeringDepartmentAdmins = [
            [
                'name' => 'Architecture Admin',
                'email' => 'arc@eng.psu.edu.eg',
                'password' => 'arc@791',
                'department_name_ar' => 'قسم العمارة والتخطيط العمراني',
            ],
            [
                'name' => 'Marine Admin',
                'email' => 'mar@eng.psu.edu.eg',
                'password' => 'mar@679',
                'department_name_ar' => 'قسم الهندسة البحرية وعمارة السفن',
            ],
            [
                'name' => 'Chemical Admin',
                'email' => 'chem@eng.psu.edu.eg',
                'password' => 'chem@109',
                'department_name_ar' => 'قسم الهندسة الكيميائية',
            ],
            [
                'name' => 'Computers and Control Admin',
                'email' => 'comp@eng.psu.edu.eg',
                'password' => 'comp@654',
                'department_name_ar' => 'قسم الهندسة الكهربية - حاسبات وتحكم',
            ],
            [
                'name' => 'Electronics and Communications Admin',
                'email' => 'comm@eng.psu.edu.eg',
                'password' => 'comm@129',
                'department_name_ar' => 'قسم الهندسة الكهربية - إلكترونيات واتصالات',
            ],
            [
                'name' => 'Production and Mechanical Design Admin',
                'email' => 'prod@eng.psu.edu.eg',
                'password' => 'prod@717',
                'department_name_ar' => 'قسم هندسة الإنتاج والتصميم الميكانيكي',
            ],
            [
                'name' => 'Mechanical Power Admin',
                'email' => 'mech@eng.psu.edu.eg',
                'password' => 'mech@786',
                'department_name_ar' => 'هندسة القوى الميكانيكية',
            ],
            [
                'name' => 'Natural Gas Admin',
                'email' => 'gaz@eng.psu.edu.eg',
                'password' => 'gaz@701',
                'department_name_ar' => 'برنامج هندسة الغاز الطبيعي',
            ],
            [
                'name' => 'Construction Admin',
                'email' => 'cons@eng.psu.edu.eg',
                'password' => 'cons@702',
                'department_name_ar' => 'برنامج هندسة تشييد',
            ],
            [
                'name' => 'Civil Admin',
                'email' => 'civ@eng.psu.edu.eg',
                'password' => 'civ@813',
                'department_name_ar' => 'قسم الهندسة المدنية',
            ],
        ];

        foreach ($engineeringDepartmentAdmins as $adminData) {
            $department = Department::where('faculty_id', $faculty->id)
                ->where('name_ar', $adminData['department_name_ar'])
                ->first();

            if (!$department) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make($adminData['password']),
                    'role' => 'department_admin',
                    'faculty_id' => $faculty->id,
                    'department_id' => $department->id,
                ]
            );
        }
    }
}