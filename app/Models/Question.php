<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $fillable = [
        'survey_id',
        'survey_section_id',
        'question_text',
        'type',
        'is_required',
        'display_order',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('display_order');
    }
    public function section()
    {
        return $this->belongsTo(SurveySection::class, 'survey_section_id');
    }
}