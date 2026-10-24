<?php

namespace App\Models;

use App\Enums\AlertSource;
use App\Enums\TideLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'source',
        'level',
        'water_level',
        'title',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => AlertSource::class,
            'level' => TideLevel::class,
            'water_level' => 'float',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
