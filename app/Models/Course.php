<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'department_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // كل مقرر تابع لقسم واحد
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // المقرر ممكن يتطرح أكثر من مرة (سنوات / ترم / فرق)
    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (اختياري لكن مفيد جدًا)
    |--------------------------------------------------------------------------
    */

    // اسم المقرر مع الكود
    public function getFullNameAttribute()
    {
        return $this->name_ar . ($this->code ? ' (' . $this->code . ')' : '');
    }
}