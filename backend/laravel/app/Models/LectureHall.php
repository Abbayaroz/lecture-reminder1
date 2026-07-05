<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LectureHall extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lecture_halls';

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'capacity',
        'location',
        'building',
        'floor',
        'features',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'features' => 'json',
    ];

    /**
     * Relationships
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
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

    public function scopeByCapacity($query, $minCapacity)
    {
        return $query->where('capacity', '>=', $minCapacity);
    }
}
