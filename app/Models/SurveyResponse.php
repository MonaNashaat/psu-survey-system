<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    
    protected $fillable = [
        'survey_id',
        'response_token',
        'ip_address',
        'device_hash',
        'user_agent',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }


    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}