<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    public const OWNER_QUALITY_CENTER = 'quality_center';

    public const OWNER_PRESIDENCY = 'presidency';

    protected $fillable = [
        'title',
        'description',

        'scope_level',
        'faculty_id',
        'department_id',

        'course_offering_id',
        'course_title',
        'department_name',
        'semester',
        'level',
        'academic_year',
        'survey_owner',

        'start_date',
        'end_date',
        'is_active',
        'expected_responses',
        'auto_close_on_limit',
        'allow_multiple_submissions',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('display_order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function sections()
    {
        return $this->hasMany(SurveySection::class)->orderBy('display_order');
    }

    public function permissions()
    {
        return $this->hasMany(SurveyPermission::class);
    }

    public function permittedUsers()
    {
        return $this->belongsToMany(User::class, 'survey_permissions')
            ->withPivot('permission_type')
            ->withTimestamps();
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

    public function isCourseSurvey(): bool
    {
        return !is_null($this->course_offering_id);
    }

    public function isGeneralSurvey(): bool
    {
        return is_null($this->course_offering_id);
    }

    public function hasReachedResponseLimit(): bool
    {
        if (!$this->expected_responses || $this->expected_responses < 1) {
            return false;
        }

        return $this->responses()->count() >= $this->expected_responses;
    }

    public function isAvailableForSubmission(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->auto_close_on_limit && $this->hasReachedResponseLimit()) {
            return false;
        }

        return true;
    }
    public function isQualityCenterSurvey(): bool
    {
        return $this->survey_owner === self::OWNER_QUALITY_CENTER;
    }

    public function isPresidencySurvey(): bool
    {
        return $this->survey_owner === self::OWNER_PRESIDENCY;
    }
}
