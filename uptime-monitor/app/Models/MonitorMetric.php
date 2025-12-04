<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitor_id',
        'period_start',
        'period_end',
        'avg_response_time_ms',
        'p95_response_time_ms',
        'uptime_seconds',
        'downtime_seconds',
        'checks_count',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'avg_response_time_ms' => 'decimal:2',
        'p95_response_time_ms' => 'decimal:2',
    ];

    // Relationships
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    // Helper methods
    public function getUptimePercentageAttribute(): float
    {
        $totalSeconds = $this->uptime_seconds + $this->downtime_seconds;
        
        if ($totalSeconds === 0) {
            return 0;
        }
        
        return round(($this->uptime_seconds / $totalSeconds) * 100, 2);
    }
}
