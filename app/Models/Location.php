<?php

namespace App\Models;

use App\Enums\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'region'];

    protected function casts(): array
    {
        return ['region' => Region::class];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** Pencarian nama + filter wilayah untuk pemilih lokasi. */
    public function scopeSearch(Builder $query, ?string $term, ?string $region): Builder
    {
        return $query
            ->when($term, fn (Builder $q, string $t) => $q->where('name', 'like', '%'.addcslashes($t, '%_\\').'%'))
            ->when($region, fn (Builder $q, string $r) => $q->where('region', $r));
    }
}
