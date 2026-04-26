<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->enum('survey_type', ['institutional', 'department'])
                ->default('institutional')
                ->after('description');

            $table->foreignId('department_id')
                ->nullable()
                ->after('survey_type')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('survey_type');
        });
    }
};