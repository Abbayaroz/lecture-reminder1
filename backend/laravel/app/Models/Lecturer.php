<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'department_id',
        'title',
        'specialization',
        'qualifications',
        'office_location',
        'office_phone',
        'employment_date',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'employment_date' => 'date',
        'approval_date' => 'datetime',
        'is_approved' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courses()
    {
        return $this->hasMany(CourseAllocation::class, 'lecturer_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function lectureNotes()
    {
        return $this->hasMany(LectureNote::class, 'uploaded_by', 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
