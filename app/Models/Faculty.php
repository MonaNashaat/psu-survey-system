<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function surveyTemplates()
    {
        return $this->hasMany(SurveyTemplate::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: ($this->name_en ?: '');
    }
}