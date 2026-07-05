<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_courses';

    protected $fillable = [
        'student_id',
        'course_allocation_id',
        'enrollment_date',
        'enrollment_status',
        'final_score',
        'grade',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'final_score' => 'float',
    ];

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseAllocation()
    {
        return $this->belongsTo(CourseAllocation::class);
    }

    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            CourseAllocation::class,
            'id',
            'id',
            'course_allocation_id',
            'course_id'
        );
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
