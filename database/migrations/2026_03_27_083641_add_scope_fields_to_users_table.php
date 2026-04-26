<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'scope_level')) {
                $table->enum('scope_level', ['university', 'faculty', 'department'])
                    ->default('faculty')
                    ->after('description');
            }

            if (!Schema::hasColumn('surveys', 'faculty_id')) {
                $table->foreignId('faculty_id')
                    ->nullable()
                    ->after('scope_level')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('surveys', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('faculty_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        // ترحيل مبدئي من النظام القديم
        if (Schema::hasColumn('surveys', 'survey_type')) {
            $surveys = DB::table('surveys')->get();

            foreach ($surveys as $survey) {
                $scopeLevel = 'faculty';
                $facultyId = null;
                $departmentId = $survey->department_id ?? null;

                if (($survey->survey_type ?? null) === 'department') {
                    $scopeLevel = 'department';
                } else {
                    $scopeLevel = 'faculty';
                }

                // لو survey مربوط بـ course_offering نستنتج القسم والكلية
                if (!empty($survey->course_offering_id)) {
                    $course = DB::table('course_offerings')
                        ->join('courses', 'courses.id', '=', 'course_offerings.course_id')
                        ->leftJoin('departments', 'departments.id', '=', 'courses.department_id')
                        ->select('courses.department_id', 'departments.faculty_id')
                        ->where('course_offerings.id', $survey->course_offering_id)
                        ->first();

                    if ($course) {
                        if (!$departmentId && !empty($course->department_id)) {
                            $departmentId = $course->department_id;
                        }

                        if (!empty($course->faculty_id)) {
                            $facultyId = $course->faculty_id;
                        }

                        $scopeLevel = 'department';
                    }
                }

                // لو عنده department_id فقط نستنتج faculty_id
                if (!$facultyId && $departmentId) {
                    $facultyId = DB::table('departments')
                        ->where('id', $departmentId)
                        ->value('faculty_id');
                }

                DB::table('surveys')
                    ->where('id', $survey->id)
                    ->update([
                        'scope_level' => $scopeLevel,
                        'faculty_id' => $facultyId,
                        'department_id' => $departmentId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }

            if (Schema::hasColumn('surveys', 'faculty_id')) {
                $table->dropConstrainedForeignId('faculty_id');
            }

            if (Schema::hasColumn('surveys', 'scope_level')) {
                $table->dropColumn('scope_level');
            }
        });
    }
};