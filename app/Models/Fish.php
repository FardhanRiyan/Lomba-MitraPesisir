<?php

namespace App\Models;

use App\Enums\FishRarity;
use App\Enums\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Fish extends Model
{
    protected $table = 'fish';

    protected $fillable = ['name', 'description', 'rarity', 'region', 'image_url'];

    protected function casts(): array
    {
        return [
            'rarity' => FishRarity::class,
            'region' => Region::class,
        ];
    }

    public function photoUrl(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        return filter_var($this->image_url, FILTER_VALIDATE_URL)
            ? $this->image_url
            : Storage::disk('public')->url($this->image_url);
    }

    public function scopeSearch(Builder $query, ?string $term, ?string $region): Builder
    {
        return $query
            ->when($term, fn (Builder $q, string $t) => $q->where('name', 'like', '%'.addcslashes($t, '%_\\').'%'))
            ->when($region, fn (Builder $q, string $r) => $q->where('region', $r));
    }
}
