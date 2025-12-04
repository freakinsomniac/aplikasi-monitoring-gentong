<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitor_id',
        'checked_at',
        'status',
        'latency_ms',
        'http_status',
        'error_message',
        'response_size',
        'region',
        'meta',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'meta' => 'array',
    ];

    // Relationships
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    // Helper methods
    public function isUp(): bool
    {
        return $this->status === 'up';
    }

    public function isDown(): bool
    {
        return $this->status === 'down';
    }
}
