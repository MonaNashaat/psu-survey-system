<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplateSection extends Model
{
    protected $fillable = [
        'survey_template_id',
        'title',
        'display_order',
    ];

    public function template()
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyTemplateQuestion::class)->orderBy('display_order');
    }
}