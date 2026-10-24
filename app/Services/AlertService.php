<?php

namespace App\Services;

use App\Enums\TideLevel;
use App\Models\Alert;
use App\Models\Location;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Logika peringatan/anomali: membuat notifikasi (tabel alerts) saat status air laut
 * berubah menjadi Waspada/Bahaya atau saat ada laporan berisiko di lokasi pengguna.
 */
final class AlertService
{
    /** Peringatan kondisi yang sama tidak diulang dalam rentang ini (jam). */
    public const DEDUPE_HOURS = 3;

    public function __construct(private readonly TideSimulator $tide)
    {
    }

    /**
     * Buat peringatan kondisi air laut untuk satu pengguna bila perlu.
     * Mengembalikan alert baru, atau null jika tidak perlu (aman / dimatikan / sudah ada).
     */
    public function syncForUser(User $user, ?Location $location = null, ?array $status = null): ?Alert
    {
        if (! $user->notif_kondisi) {
            return null;
        }
        $location ??= $user->location;
        if ($location === null) {
            return null;
        }

        $status ??= $this->tide->status($location->id);
        /** @var TideLevel $level */
        $level = $status['level'];
        if (! $level->needsAlert()) {
            return null;
        }

        $recent = Alert::query()
            ->where('user_id', $user->id)
            ->where('location_id', $location->id)
            ->where('source', AlertSource::KONDISI->value)
            ->where('level', $level->value)
            ->where('created_at', '>=', CarbonImmutable::now()->subHours(self::DEDUPE_HOURS))
            ->exists();
        if ($recent) {
            return null;
        }

        $meters = number_format($status['waterLevel'], 2, ',', '.');

        return Alert::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'source' => AlertSource::KONDISI,
            'level' => $level,
            'water_level' => $status['waterLevel'],
            'title' => $level->label().': air laut '.$meters.' m di '.$location->name,
            'message' => $level->message(),
        ]);
    }

    /**
     * Periksa semua lokasi yang punya pengguna berpreferensi notifikasi aktif.
     * Dipanggil oleh scheduler (pesisir:check-tide). Mengembalikan jumlah alert baru.
     */
    public function syncAll(): int
    {
        $created = 0;
        $statusByLocation = [];

        User::query()
            ->with('location')
            ->where('notif_kondisi', true)
            ->whereNotNull('location_id')
            ->chunkById(200, function ($users) use (&$created, &$statusByLocation) {
                foreach ($users as $user) {
                    if ($user->location === null) {
                        continue;
                    }
                    $statusByLocation[$user->location_id] ??= $this->tide->status($user->location_id);
                    if ($this->syncForUser($user, $user->location, $statusByLocation[$user->location_id])) {
                        $created++;
                    }
                }
            });

        return $created;
    }

}
