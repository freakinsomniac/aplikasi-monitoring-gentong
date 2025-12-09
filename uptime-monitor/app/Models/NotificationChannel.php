<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'config',
        'created_by',
        'is_enabled',
    ];

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
