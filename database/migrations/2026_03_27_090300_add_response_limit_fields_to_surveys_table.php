<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'expected_responses')) {
                $table->unsignedInteger('expected_responses')
                    ->nullable()
                    ->after('allow_multiple_submissions');
            }

            if (!Schema::hasColumn('surveys', 'auto_close_on_limit')) {
                $table->boolean('auto_close_on_limit')
                    ->default(true)
                    ->after('expected_responses');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'auto_close_on_limit')) {
                $table->dropColumn('auto_close_on_limit');
            }

            if (Schema::hasColumn('surveys', 'expected_responses')) {
                $table->dropColumn('expected_responses');
            }
        });
    }
};