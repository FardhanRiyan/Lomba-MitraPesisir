<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\User;
use App\Services\TideSimulator;
use App\Services\WhatsAppCloudApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckExtremeWeather extends Command
{
    protected $signature = 'pesisir:check-extreme-weather';

    protected $description = 'Periksa cuaca ekstrem dan kirim peringatan WhatsApp kepada pengguna yang opt-in';

    public function handle(TideSimulator $tide, WhatsAppCloudApi $whatsapp): int
    {
        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $configurationError = false;
        $windThreshold = (int) config('weather.alert.wind_speed_kmh');
        $waveThreshold = (float) config('weather.alert.wave_height_m');
        $dedupeHours = max(1, (int) config('weather.alert.dedupe_hours'));
        if ($windThreshold <= 0 || $waveThreshold <= 0) {
            $this->error('Ambang cuaca ekstrem harus lebih besar dari nol.');
            Log::error('Ambang cuaca ekstrem tidak valid.', [
                'wind_speed_kmh' => $windThreshold,
                'wave_height_m' => $waveThreshold,
            ]);

            return self::FAILURE;
        }

        Location::query()->orderBy('id')->chunkById(100, function ($locations) use (
            $tide,
            $whatsapp,
            $windThreshold,
            $waveThreshold,
            $dedupeHours,
            &$sent,
            &$failed,
            &$skipped,
            &$configurationError,
        ) {
            foreach ($locations as $location) {
                try {
                    $status = $tide->status($location->id);
                } catch (\Throwable $exception) {
                    Log::error('Gagal memeriksa kondisi cuaca lokasi.', [
                        'location_id' => $location->id,
                        'exception' => $exception::class,
                    ]);
                    $failed++;

                    continue;
                }

                $windExtreme = $status['windSpeedKmh'] >= $windThreshold;
                $waveExtreme = $status['waveHeightM'] >= $waveThreshold;
                if (! $windExtreme && ! $waveExtreme) {
                    continue;
                }

                $recipients = User::query()
                    ->where('location_id', $location->id)
                    ->where('whatsapp_opt_in', true);

                if (! $recipients->exists()) {
                    continue;
                }

                if (! $whatsapp->isConfigured()) {
                    $this->error('Konfigurasi Meta WhatsApp Cloud API belum lengkap atau tidak valid.');
                    Log::error('Peringatan cuaca ekstrem tidak dikirim karena konfigurasi WhatsApp tidak valid.', [
                        'location_id' => $location->id,
                    ]);
                    $configurationError = true;

                    return false;
                }

                $conditions = implode('-', array_filter([
                    $windExtreme ? 'angin' : null,
                    $waveExtreme ? 'gelombang' : null,
                ]));
                $message = sprintf(
                    'PERINGATAN DINI CUACA - %s. Angin: %d km/jam; gelombang: %.1f m; tinggi air: %.2f m. Waktu: %s WIB. Mohon waspada dan utamakan keselamatan.',
                    $location->name,
                    $status['windSpeedKmh'],
                    $status['waveHeightM'],
                    $status['waterLevel'],
                    now()->format('d/m/Y H:i'),
                );

                $recipients->select(['id', 'name', 'whatsapp_number', 'whatsapp_opt_in', 'location_id'])
                    ->chunkById(100, function ($users) use (
                        $whatsapp,
                        $location,
                        $conditions,
                        $message,
                        $dedupeHours,
                        &$sent,
                        &$failed,
                        &$skipped,
                    ): void {
                        foreach ($users as $user) {
                            $dedupeKey = sprintf(
                                'weather-alert:%d:%d:%s',
                                $location->id,
                                $user->id,
                                $conditions,
                            );
                            $lockKey = $dedupeKey.':sending';

                            if (! Cache::add($lockKey, true, now()->addMinutes(5))) {
                                $skipped++;

                                continue;
                            }

                            try {
                                if (Cache::has($dedupeKey)) {
                                    $skipped++;

                                    continue;
                                }

                                if ($whatsapp->sendTemplate($user, $message)) {
                                    Cache::put($dedupeKey, true, now()->addHours($dedupeHours));
                                    $sent++;
                                } else {
                                    $failed++;
                                }
                            } finally {
                                Cache::forget($lockKey);
                            }
                        }
                    });
            }
        });

        $this->info("Selesai. Terkirim: {$sent}; gagal: {$failed}; dilewati: {$skipped}.");

        return $configurationError || $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
