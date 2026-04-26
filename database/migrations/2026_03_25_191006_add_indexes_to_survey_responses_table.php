<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->index(['survey_id', 'device_hash']);
            $table->index(['survey_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropIndex(['survey_id', 'device_hash']);
            $table->dropIndex(['survey_id', 'ip_address']);
        });
    }
};