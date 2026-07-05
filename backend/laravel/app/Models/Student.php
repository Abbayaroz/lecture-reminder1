<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'registration_number',
        'programme_id',
        'level_id',
        'admission_date',
        'expected_graduation',
        'gpa',
        'enrollment_status',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'expected_graduation' => 'date',
        'gpa' => 'float',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function courses()
    {
        return $this->hasMany(StudentCourse::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeByProgramme($query, $programmeId)
    {
        return $query->where('programme_id', $programmeId);
    }

    public function scopeByLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }
}
