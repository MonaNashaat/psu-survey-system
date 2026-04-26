<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasScopeLevel = Schema::hasColumn('surveys', 'scope_level');
        $hasFacultyId = Schema::hasColumn('surveys', 'faculty_id');
        $hasDepartmentId = Schema::hasColumn('surveys', 'department_id');
        $hasSurveyType = Schema::hasColumn('surveys', 'survey_type');

        Schema::table('surveys', function (Blueprint $table) use ($hasScopeLevel, $hasFacultyId, $hasDepartmentId) {
            if (!$hasScopeLevel) {
                $table->enum('scope_level', ['university', 'faculty', 'department'])
                    ->default('faculty')
                    ->after('description');
            }

            if (!$hasFacultyId) {
                $table->foreignId('faculty_id')
                    ->nullable()
                    ->after('scope_level')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!$hasDepartmentId) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('faculty_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        if ($hasSurveyType) {
            $surveys = DB::table('surveys')->get();

            foreach ($surveys as $survey) {
                $scopeLevel = 'faculty';
                $facultyId = $survey->faculty_id ?? null;
                $departmentId = $survey->department_id ?? null;

                if (($survey->survey_type ?? null) === 'department') {
                    $scopeLevel = 'department';
                } else {
                    $scopeLevel = 'faculty';
                }

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

                        if (!$facultyId && !empty($course->faculty_id)) {
                            $facultyId = $course->faculty_id;
                        }

                        $scopeLevel = 'department';
                    }
                }

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
        $hasFacultyId = Schema::hasColumn('surveys', 'faculty_id');
        $hasDepartmentId = Schema::hasColumn('surveys', 'department_id');
        $hasScopeLevel = Schema::hasColumn('surveys', 'scope_level');

        Schema::table('surveys', function (Blueprint $table) use ($hasFacultyId, $hasDepartmentId, $hasScopeLevel) {
            if ($hasDepartmentId) {
                $table->dropConstrainedForeignId('department_id');
            }

            if ($hasFacultyId) {
                $table->dropConstrainedForeignId('faculty_id');
            }

            if ($hasScopeLevel) {
                $table->dropColumn('scope_level');
            }
        });
    }
};