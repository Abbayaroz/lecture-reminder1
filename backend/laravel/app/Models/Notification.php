<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'timetable_id',
        'type',
        'title',
        'message',
        'data',
        'notification_channel',
        'is_read',
        'read_at',
        'scheduled_at',
        'sent_at',
        'is_sent',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
        'data' => 'json',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class)->nullable();
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
