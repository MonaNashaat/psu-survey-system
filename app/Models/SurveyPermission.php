<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyPermission extends Model
{
    protected $fillable = [
        'survey_id',
        'user_id',
        'permission_type',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}