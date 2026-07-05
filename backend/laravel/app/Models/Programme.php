<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'description',
        'duration_years',
        'programme_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
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
        return $query->where('programme_type', $type);
    }
}
