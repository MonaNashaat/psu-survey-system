<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('survey_templates', 'scope_level')) {
                $table->enum('scope_level', ['university', 'faculty', 'department'])
                    ->default('faculty')
                    ->after('description');
            }

            if (!Schema::hasColumn('survey_templates', 'faculty_id')) {
                $table->foreignId('faculty_id')
                    ->nullable()
                    ->after('scope_level')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('survey_templates', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('faculty_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('survey_templates', 'template_type')) {
            $templates = DB::table('survey_templates')->get();

            foreach ($templates as $template) {
                $scopeLevel = ($template->template_type ?? null) === 'department' ? 'department' : 'faculty';
                $facultyId = null;
                $departmentId = $template->department_id ?? null;

                if ($departmentId) {
                    $facultyId = DB::table('departments')
                        ->where('id', $departmentId)
                        ->value('faculty_id');
                }

                DB::table('survey_templates')
                    ->where('id', $template->id)
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
        Schema::table('survey_templates', function (Blueprint $table) {
            if (Schema::hasColumn('survey_templates', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }

            if (Schema::hasColumn('survey_templates', 'faculty_id')) {
                $table->dropConstrainedForeignId('faculty_id');
            }

            if (Schema::hasColumn('survey_templates', 'scope_level')) {
                $table->dropColumn('scope_level');
            }
        });
    }
};