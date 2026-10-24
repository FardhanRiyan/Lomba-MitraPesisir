<?php

namespace App\Models;

use App\Enums\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatchLog extends Model
{
    protected $fillable = ['user_id', 'location_id', 'fish_name', 'quantity', 'note', 'region', 'image_path'];

    protected function casts(): array
    {
        return ['region' => Region::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
