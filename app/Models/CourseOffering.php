<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    protected $fillable = [
        'course_id',
        'academic_year',
        'semester',
        'level',
        'instructor_name',
        'assistant_name',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }
}