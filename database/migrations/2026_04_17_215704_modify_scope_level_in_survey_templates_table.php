<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE survey_templates
            MODIFY scope_level ENUM('university', 'faculty', 'department', 'course') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE survey_templates
            MODIFY scope_level ENUM('university', 'faculty', 'department') NOT NULL
        ");
    }
};