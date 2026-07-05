<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableOfficer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'timetable_officers';

    protected $fillable = [
        'user_id',
        'department_id',
        'faculty_id',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
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
        return $this->belongsTo(Department::class)->nullable();
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class)->nullable();
    }

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
