<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LectureNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lecture_notes';

    protected $fillable = [
        'course_allocation_id',
        'timetable_id',
        'title',
        'description',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
        'is_published',
        'publish_date',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publish_date' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function courseAllocation()
    {
        return $this->belongsTo(CourseAllocation::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class)->nullable();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCourseAllocation($query, $allocationId)
    {
        return $query->where('course_allocation_id', $allocationId);
    }
}
