<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplateQuestion extends Model
{
    protected $fillable = [
        'survey_template_id',
        'survey_template_section_id',
        'question_text',
        'type',
        'is_required',
        'display_order',
    ];

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function section()
    {
        return $this->belongsTo(SurveyTemplateSection::class, 'survey_template_section_id');
    }

    public function options()
    {
        return $this->hasMany(SurveyTemplateQuestionOption::class, 'survey_template_question_id')
            ->orderBy('display_order');
    }
}