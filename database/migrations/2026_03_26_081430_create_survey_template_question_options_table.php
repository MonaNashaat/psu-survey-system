<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_template_question_options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('survey_template_question_id');
            $table->string('option_text');
            $table->integer('option_value')->nullable();
            $table->integer('display_order')->default(1);
            $table->timestamps();

            $table->foreign('survey_template_question_id', 'stqo_question_fk')
                ->references('id')
                ->on('survey_template_questions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('survey_template_question_options', function (Blueprint $table) {
            $table->dropForeign('stqo_question_fk');
        });

        Schema::dropIfExists('survey_template_question_options');
    }
};