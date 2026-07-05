<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'code',
        'name',
        'description',
        'hod_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id')->nullable();
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function lecturers()
    {
        return $this->hasMany(Lecturer::class);
    }

    public function lectureHalls()
    {
        return $this->hasMany(LectureHall::class);
    }

    public function timetableOfficers()
    {
        return $this->hasMany(TimetableOfficer::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }
}
