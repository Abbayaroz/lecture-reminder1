<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_session_id',
        'course_allocation_id',
        'lecture_hall_id',
        'level_id',
        'day_of_week',
        'start_time',
        'end_time',
        'semester',
        'status',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
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

    public function lecturer()
    {
        return $this->hasOneThrough(
            User::class,
            CourseAllocation::class,
            'id',
            'id',
            'course_allocation_id',
            'lecturer_id'
        );
    }

    public function lectureHall()
    {
        return $this->belongsTo(LectureHall::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function lectureNotes()
    {
        return $this->hasMany(LectureNote::class);
    }

    /**
     * Scopes
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('academic_session_id', $sessionId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')->orderBy('start_time', 'asc');
    }
}
