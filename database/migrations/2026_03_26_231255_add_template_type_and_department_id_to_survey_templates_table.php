<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_templates', function (Blueprint $table) {
            $table->enum('template_type', ['institutional', 'department'])
                ->default('institutional')
                ->after('description');

            $table->foreignId('department_id')
                ->nullable()
                ->after('template_type')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('survey_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('template_type');
        });
    }
};