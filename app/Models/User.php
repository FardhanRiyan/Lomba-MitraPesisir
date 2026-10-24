<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'whatsapp_number',
        'password',
        'role',
        'location_id',
        'notif_kondisi',
        'whatsapp_opt_in',
        'mode_offline',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'password' => 'hashed',
            'notif_kondisi' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
            'mode_offline' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function catchLogs(): HasMany
    {
        return $this->hasMany(CatchLog::class);
    }

    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /** User and admin may submit coastal reports. */
    public function canCreateReports(): bool
    {
        return in_array($this->role, [UserRole::ADMIN, UserRole::USER], true);
    }

    public function scopeSearch(Builder $query, ?string $term, ?string $role): Builder
    {
        return $query
            ->when($term, function (Builder $q, string $t) {
                $like = '%'.addcslashes($t, '%_\\').'%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $like)->orWhere('whatsapp_number', 'like', $like));
            })
            ->when($role, fn (Builder $q, string $r) => $q->where('role', $r));
    }
}
