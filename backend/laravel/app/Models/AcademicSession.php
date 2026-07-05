<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_sessions';

    protected $fillable = [
        'title',
        'code',
        'start_date',
        'end_date',
        'first_semester_start',
        'first_semester_end',
        'second_semester_start',
        'second_semester_end',
        'is_current',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'first_semester_start' => 'date',
        'first_semester_end' => 'date',
        'second_semester_start' => 'date',
        'second_semester_end' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function courseAllocations()
    {
        return $this->hasMany(CourseAllocation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)->first();
    }
}
