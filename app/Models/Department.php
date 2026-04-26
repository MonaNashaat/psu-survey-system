<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'faculty_id',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function surveyTemplates()
    {
        return $this->hasMany(SurveyTemplate::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: ($this->name_en ?: '');
    }
}