<?php

namespace App\Models;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'category',
        'severity',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'category' => ReportCategory::class,
            'severity' => ReportSeverity::class,
            'status' => ReportStatus::class,
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

    /**
     * Pencarian & filter daftar laporan.
     * Kunci filter: q, category, severity, status, location_id (semuanya opsional).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn (Builder $w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($filters['category'] ?? null, fn (Builder $q, string $v) => $q->where('category', $v))
            ->when($filters['severity'] ?? null, fn (Builder $q, string $v) => $q->where('severity', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when($filters['location_id'] ?? null, fn (Builder $q, int $v) => $q->where('location_id', $v));
    }
}
