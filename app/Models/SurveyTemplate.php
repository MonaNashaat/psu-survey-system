<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',

        'scope_level',
        'faculty_id',
        'department_id',

        'is_active',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sections()
    {
        return $this->hasMany(SurveyTemplateSection::class)->orderBy('display_order');
    }

    public function questions()
    {
        return $this->hasMany(SurveyTemplateQuestion::class)->orderBy('display_order');
    }

    public function isUniversityLevel(): bool
    {
        return $this->scope_level === 'university';
    }

    public function isFacultyLevel(): bool
    {
        return $this->scope_level === 'faculty';
    }

    public function isDepartmentLevel(): bool
    {
        return $this->scope_level === 'department';
    }
}