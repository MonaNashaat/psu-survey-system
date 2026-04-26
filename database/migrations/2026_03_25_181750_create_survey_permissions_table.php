<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission_type')->default('view_results');
            $table->timestamps();

            $table->unique(['survey_id', 'user_id', 'permission_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_permissions');
    }
};