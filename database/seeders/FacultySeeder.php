<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            ['name_ar' => 'كلية العلاج الطبيعي', 'name_en' => 'Faculty of Physical Therapy'],
            ['name_ar' => 'كلية الآداب', 'name_en' => 'Faculty of Arts'],
            ['name_ar' => 'كلية التجارة', 'name_en' => 'Faculty of Commerce'],
            ['name_ar' => 'كلية التربية النوعية', 'name_en' => 'Faculty of Specific Education'],
            ['name_ar' => 'كلية التربية', 'name_en' => 'Faculty of Education'],
            ['name_ar' => 'كلية التمريض', 'name_en' => 'Faculty of Nursing'],
            ['name_ar' => 'كلية الحقوق', 'name_en' => 'Faculty of Law'],
            ['name_ar' => 'كلية الصيدلة', 'name_en' => 'Faculty of Pharmacy'],
            ['name_ar' => 'كلية الطب', 'name_en' => 'Faculty of Medicine'],
            ['name_ar' => 'كلية العلوم', 'name_en' => 'Faculty of Science'],
            ['name_ar' => 'كلية الهندسة', 'name_en' => 'Faculty of Engineering'],
            ['name_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_en' => 'Faculty of Management Technology and Information Systems'],
            ['name_ar' => 'كلية رياض الأطفال', 'name_en' => 'Faculty of Early Childhood Education'],
            ['name_ar' => 'كلية علوم الرياضة', 'name_en' => 'Faculty of Sports Sciences'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::updateOrCreate(
                ['name_ar' => $faculty['name_ar']],
                $faculty
            );
        }
    }
}