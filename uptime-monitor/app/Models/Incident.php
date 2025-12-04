<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitor_id',
        'started_at',
        'ended_at',
        'resolved',
        'description',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'resolved' => 'boolean',
    ];

    // Relationships
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    // Helper methods
    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }
        
        return $this->ended_at->diffInSeconds($this->started_at);
    }

    public function isOngoing(): bool
    {
        return !$this->resolved && !$this->ended_at;
    }
}
