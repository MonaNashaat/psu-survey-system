<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty_id',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function surveyPermissions()
    {
        return $this->hasMany(SurveyPermission::class);
    }

    public function permittedSurveys()
    {
        return $this->belongsToMany(Survey::class, 'survey_permissions')
            ->withPivot('permission_type')
            ->withTimestamps();
    }

    public function isUniversityAdmin(): bool
    {
        return $this->role === 'university_admin';
    }
    public function isPresidencyAdmin(): bool
    {
        return $this->role === 'presidency_admin';
    }

    public function isFacultyAdmin(): bool
    {
        return $this->role === 'faculty_admin';
    }

    public function isDepartmentAdmin(): bool
    {
        return $this->role === 'department_admin';
    }

    public function isResultsViewer(): bool
    {
        return $this->role === 'results_viewer';
    }

    /**
     * عامّة لأي مستخدم إداري.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [
            'university_admin',
            'presidency_admin',
            'faculty_admin',
            'department_admin',
        ], true);
    }

    /**
     * للتوافق المؤقت لو كان عندك كود قديم يعتمد على الاسم القديم.
     */
    public function isDepartmentManager(): bool
    {
        return $this->isDepartmentAdmin();
    }
    public function roleLabel(): string
    {
        return match ($this->role) {
            'university_admin' => 'أدمن جامعة',
            'presidency_admin' => 'المكتب الفني لرئيس الجامعة',
            'faculty_admin' => 'أدمن كلية',
            'department_admin' => 'أدمن قسم',
            'results_viewer' => 'عرض نتائج فقط',
            default => $this->role,
        };
    }
    public function canViewSurveyResults(Survey $survey): bool
    {
        if ($this->isUniversityAdmin()) {
            return $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_QUALITY_CENTER;
        }
    
        if ($this->isPresidencyAdmin()) {
            return $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_PRESIDENCY;
        }
    
        if ($this->isFacultyAdmin()) {
            return in_array($survey->scope_level, ['faculty', 'department'], true)
                && (int) $survey->faculty_id === (int) $this->faculty_id;
        }
    
        if ($this->isDepartmentAdmin()) {
            return $survey->scope_level === 'department'
                && (int) $survey->department_id === (int) $this->department_id;
        }
    
        if ($this->isResultsViewer()) {
            return $this->surveyPermissions()
                ->where('survey_id', $survey->id)
                ->where('permission_type', 'view_results')
                ->exists();
        }
    
        return false;
    }
}
