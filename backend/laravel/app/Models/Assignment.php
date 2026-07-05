<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_allocation_id',
        'title',
        'description',
        'assignment_type',
        'due_date',
        'release_date',
        'total_marks',
        'created_by',
        'is_published',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'release_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function courseAllocation()
    {
        return $this->belongsTo(CourseAllocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('is_published', true)
            ->where('due_date', '>', now())
            ->orderBy('due_date', 'asc');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_published', true)
            ->where('due_date', '<', now());
    }
}
