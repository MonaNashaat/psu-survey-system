<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['faculty_ar' => 'كلية العلاج الطبيعي', 'name_ar' => 'العلاج الطبيعي', 'name_en' => 'Physical Therapy'],

            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم الآثار الإسلامية', 'name_en' => 'Department of Islamic Archaeology'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم علم الاجتماع', 'name_en' => 'Department of Sociology'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم التاريخ والحضارة', 'name_en' => 'Department of History and Civilization'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم الجغرافيا والخرائط', 'name_en' => 'Department of Geography and Cartography'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم الجغرافيا ونظم المعلومات الجغرافية', 'name_en' => 'Department of Geography and Geographic Information Systems'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم الفلسفة', 'name_en' => 'Department of Philosophy'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم اللغة الإنجليزية وآدابها', 'name_en' => 'Department of English Language and Literature'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم اللغة العربية وآدابها', 'name_en' => 'Department of Arabic Language and Literature'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم اللغة الفرنسية وآدابها', 'name_en' => 'Department of French Language and Literature'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم اللغة الصينية', 'name_en' => 'Department of Chinese Language'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'برنامج المساحة والخرائط ونظم المعلومات الجغرافية', 'name_en' => 'Program of Surveying, Maps, and Geographic Information Systems'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'برنامج الترجمة التخصصية باللغة الإنجليزية', 'name_en' => 'Specialized Translation Program (English)'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم الجغرافيا', 'name_en' => 'Department of Geography'],
            ['faculty_ar' => 'كلية الآداب', 'name_ar' => 'قسم علم النفس', 'name_en' => 'Department of Psychology'],

            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص المحاسبة', 'name_en' => 'Accounting Major'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص إدارة الأعمال', 'name_en' => 'Business Administration Major'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'برنامج الدراسة باللغة الإنجليزية', 'name_en' => 'English Program'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص الإحصاء والحاسب', 'name_en' => 'Statistics and Computer Science Major'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص التأمين', 'name_en' => 'Insurance Major'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص العلوم السياسية', 'name_en' => 'Political Science Major'],
            ['faculty_ar' => 'كلية التجارة', 'name_ar' => 'تخصص تدريس المواد التجارية', 'name_en' => 'Commercial Education Major'],

            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'برنامج أخصائي المعلوماتية التعليمية (باللغة الإنجليزية)', 'name_en' => 'Educational Informatics Specialist Program (English)'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'قسم الاقتصاد المنزلي التربوي', 'name_en' => 'Department of Home Economics Education'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'قسم الإعلام التربوي', 'name_en' => 'Department of Educational Media'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'قسم التربية الفنية', 'name_en' => 'Department of Art Education'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'قسم التربية الموسيقية', 'name_en' => 'Department of Music Education'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'برنامج إعداد أخصائي الصحافة والإذاعة والتليفزيون', 'name_en' => 'Program for Preparing Radio, TV, and Journalism Specialists'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'برنامج إعداد أخصائي تكنولوجيا التعليم', 'name_en' => 'Program for Preparing Educational Technology Specialists'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'برنامج إعداد معلم الحاسب الآلي', 'name_en' => 'Program for Preparing Computer Teachers'],
            ['faculty_ar' => 'كلية التربية النوعية', 'name_ar' => 'قسم تكنولوجيا التعليم', 'name_en' => 'Department of Educational Technology'],

            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم اللغة العربية وآدابها', 'name_en' => 'Department of Arabic Language and Literature'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم اللغة الإنجليزية', 'name_en' => 'Department of English Language'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم اللغة الفرنسية', 'name_en' => 'Department of French Language'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم اللغة الألمانية', 'name_en' => 'Department of German Language'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم الرياضيات', 'name_en' => 'Department of Mathematics'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم الكيمياء', 'name_en' => 'Department of Chemistry'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم الفيزياء', 'name_en' => 'Department of Physics'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم التاريخ', 'name_en' => 'Department of History'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم الجغرافيا ونظم المعلومات الجغرافية', 'name_en' => 'Department of Geography and GIS'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم البيولوجيا والجيولوجيا', 'name_en' => 'Department of Biology and Geology'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم العلوم البيولوجية', 'name_en' => 'Department of Biological Sciences'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم الفلسفة والاجتماع', 'name_en' => 'Department of Philosophy and Sociology'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج التعليم الابتدائي (لغة إنجليزية)', 'name_en' => 'Primary Education Program (English)'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج التعليم الابتدائي (لغة عربية)', 'name_en' => 'Primary Education Program (Arabic)'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج التعليم الابتدائي - تربية خاصة (لغة عربية)', 'name_en' => 'Primary Education Program – Special Education (Arabic)'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج التعليم الابتدائي (علوم)', 'name_en' => 'Primary Education Program (Science)'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج إعداد معلم التربية الخاصة', 'name_en' => 'Special Education Teacher Preparation Program'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'برنامج التعليم الابتدائي (الرياضيات)', 'name_en' => 'Primary Education Program (Mathematics)'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم دراسات اجتماعية', 'name_en' => 'Department of Social Studies'],
            ['faculty_ar' => 'كلية التربية', 'name_ar' => 'قسم علم النفس', 'name_en' => 'Department of Psychology'],

            ['faculty_ar' => 'كلية التمريض', 'name_ar' => 'البرنامج المكثف في التمريض', 'name_en' => 'Intensive Nursing Program'],
            ['faculty_ar' => 'كلية التمريض', 'name_ar' => 'برنامج التمريض التخصصي', 'name_en' => 'Specialized Nursing Program'],
            ['faculty_ar' => 'كلية التمريض', 'name_ar' => 'برنامج التمريض العام', 'name_en' => 'General Nursing Program'],

            ['faculty_ar' => 'كلية الحقوق', 'name_ar' => 'البرنامج العام', 'name_en' => 'General Law Program'],

            ['faculty_ar' => 'كلية الصيدلة', 'name_ar' => 'برنامج Pharm D (الصيدلة الإكلينيكية)', 'name_en' => 'Pharm D Program (Clinical Pharmacy)'],
            ['faculty_ar' => 'كلية الصيدلة', 'name_ar' => 'التخصص العام', 'name_en' => 'General Program'],

            ['faculty_ar' => 'كلية الطب', 'name_ar' => 'برنامج الطب والجراحة', 'name_en' => 'Medicine and Surgery Program'],

            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم الفيزياء', 'name_en' => 'Department of Physics'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم الكيمياء الحيوية', 'name_en' => 'Department of Biochemistry'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج الإحصاء وعلوم البيانات', 'name_en' => 'Statistics and Data Science Program'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم التكنولوجيا الحيوية', 'name_en' => 'Department of Biotechnology'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج الرياضيات وعلوم الحاسب', 'name_en' => 'Mathematics and Computer Science Program'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج الكيمياء الصناعية والتطبيقية', 'name_en' => 'Industrial and Applied Chemistry Program'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم الميكروبيولوجي', 'name_en' => 'Department of Microbiology'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم علم النبات', 'name_en' => 'Department of Botany'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم علم الحيوان', 'name_en' => 'Department of Zoology'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم علوم البحار', 'name_en' => 'Department of Marine Sciences'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم العلوم البيئية', 'name_en' => 'Department of Environmental Sciences'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج إدارة البيئة والرصد البيئي', 'name_en' => 'Environmental Management and Monitoring Program'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج الحوسبة والمعلوماتية الحيوية', 'name_en' => 'Bioinformatics and Computing Program'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم الرياضيات والإحصاء التطبيقي', 'name_en' => 'Department of Applied Mathematics and Statistics'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم العلوم البيولوجية', 'name_en' => 'Department of Biological Sciences'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم العلوم الجيولوجية', 'name_en' => 'Department of Geological Sciences'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم العلوم الطبيعية', 'name_en' => 'Department of Natural Sciences'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'قسم الفيزياء التطبيقية', 'name_en' => 'Department of Applied Physics'],
            ['faculty_ar' => 'كلية العلوم', 'name_ar' => 'برنامج جيولوجيا البترول والغاز الطبيعي', 'name_en' => 'Petroleum and Natural Gas Geology Program'],

            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم العمارة والتخطيط العمراني', 'name_en' => 'Department of Architecture and Urban Planning'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة البحرية وعمارة السفن', 'name_en' => 'Department of Marine Engineering and Naval Architecture'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة المدنية', 'name_en' => 'Department of Civil Engineering'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة الكيميائية', 'name_en' => 'Department of Chemical Engineering'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة الكهربية - قوى وآلات كهربية', 'name_en' => 'Department of Electrical Engineering – Power and Electrical Machines'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة الكهربية - حاسبات وتحكم', 'name_en' => 'Department of Electrical Engineering – Computers and Control'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم الهندسة الكهربية - إلكترونيات واتصالات', 'name_en' => 'Department of Electrical Engineering – Electronics and Communications'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'قسم هندسة الإنتاج والتصميم الميكانيكي', 'name_en' => 'Department of Production and Mechanical Design Engineering'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'هندسة القوى الميكانيكية', 'name_en' => 'Mechanical Power Engineering'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'برنامج هندسة الغاز الطبيعي', 'name_en' => 'Natural Gas Engineering Program'],
            ['faculty_ar' => 'كلية الهندسة', 'name_ar' => 'برنامج هندسة تشييد', 'name_en' => 'Construction Engineering Program'],

            ['faculty_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_ar' => 'قسم نظم معلومات الأعمال', 'name_en' => 'Department of Business Information Systems'],
            ['faculty_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_ar' => 'قسم نظم وتكنولوجيا المعلومات', 'name_en' => 'Department of Information Systems and Technology'],
            ['faculty_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_ar' => 'برنامج إدارة نظم تكنولوجيا المعلومات', 'name_en' => 'Information Technology Management Program'],
            ['faculty_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_ar' => 'برنامج تكنولوجيا الإدارة والأعمال', 'name_en' => 'Management and Business Technology Program'],
            ['faculty_ar' => 'كلية تكنولوجيا الإدارة ونظم المعلومات', 'name_ar' => 'برنامج تكنولوجيا المحاسبة', 'name_en' => 'Accounting Technology Program'],

            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'برنامج إعداد معلم رياض الأطفال (4–6 سنوات)', 'name_en' => 'Kindergarten Teacher Preparation Program (4–6 years)'],
            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'برنامج إعداد معلم التربية الخاصة', 'name_en' => 'Special Education Teacher Preparation Program'],
            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'برنامج إعداد معلم الحضانة (2–4 سنوات)', 'name_en' => 'Nursery Teacher Preparation Program (2–4 years)'],
            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'قسم ذوي اضطراب الذاتوية', 'name_en' => 'Autism Spectrum Disorders Department'],
            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'قسم الإعاقة العقلية', 'name_en' => 'Intellectual Disability Department'],
            ['faculty_ar' => 'كلية رياض الأطفال', 'name_ar' => 'قسم صعوبات التعلم', 'name_en' => 'Learning Disabilities Department'],

            ['faculty_ar' => 'كلية علوم الرياضة', 'name_ar' => 'قسم الإدارة الرياضية', 'name_en' => 'Department of Sports Management'],
            ['faculty_ar' => 'كلية علوم الرياضة', 'name_ar' => 'قسم التدريب الرياضي', 'name_en' => 'Department of Sports Training'],
            ['faculty_ar' => 'كلية علوم الرياضة', 'name_ar' => 'قسم الرياضة المدرسية', 'name_en' => 'Department of School Sports'],
            ['faculty_ar' => 'كلية علوم الرياضة', 'name_ar' => 'برنامج إعداد معلم التربية الرياضية (باللغة الإنجليزية)', 'name_en' => 'Physical Education Teacher Preparation Program (English)'],
        ];

        foreach ($items as $item) {
            $faculty = Faculty::where('name_ar', $item['faculty_ar'])->first();

            if (!$faculty) {
                continue;
            }

            Department::updateOrCreate(
                [
                    'faculty_id' => $faculty->id,
                    'name_ar' => $item['name_ar'],
                ],
                [
                    'name_en' => $item['name_en'],
                ]
            );
        }
    }
}