<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'title',
        'description',
        'credit_units',
        'course_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_units' => 'float',
    ];

    /**
     * Relationships
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function allocations()
    {
        return $this->hasMany(CourseAllocation::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function studentCourses()
    {
        return $this->hasManyThrough(StudentCourse::class, CourseAllocation::class, 'course_id', 'course_allocation_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('course_type', $type);
    }
}
