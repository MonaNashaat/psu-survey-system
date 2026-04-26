<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplateQuestionOption extends Model
{
    protected $fillable = [
        'survey_template_question_id',
        'option_text',
        'option_value',
        'display_order',
    ];

    public function question()
    {
        return $this->belongsTo(SurveyTemplateQuestion::class, 'survey_template_question_id');
    }
}