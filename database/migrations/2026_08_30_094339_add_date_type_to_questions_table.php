<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE questions
            MODIFY COLUMN type ENUM('mcq','scale','text','date') NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE questions
            MODIFY COLUMN type ENUM('mcq','scale','text') NOT NULL
        ");
    }
};