<?php

namespace App\Services;

use App\Enums\TideLevel;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Simulasi matematis kondisi pasang-surut (bukan data sensor/pengukuran nyata).
 *
 * Tinggi air = baseline + dua gelombang sinus: semi-diurnal (periode ±12,42 jam)
 * dan diurnal (±24,84 jam). Fase/amplitudo diturunkan deterministik dari id lokasi,
 * sehingga hasil selalu sama untuk lokasi + waktu yang sama.
 */
final class TideSimulator
{
    public const PERIOD_SEMI_DIURNAL_H = 12.42;
    public const PERIOD_DIURNAL_H = 24.84;

    /** Pembulatan ala JavaScript Math.round (setengah ke atas). */
    private static function round(float $value): float
    {
        return floor($value + 0.5);
    }

    private static function seeded(float $seed): float
    {
        $x = sin($seed * 12.9898) * 43758.5453;

        return $x - floor($x);
    }

    /** @return array{baseline: float, amp1: float, amp2: float, phase1: float, phase2: float} */
    private function params(int $locationId): array
    {
        $id = $locationId > 0 ? $locationId : 1;

        return [
            'baseline' => 1.1 + self::seeded($id * 1.1) * 0.3,
            'amp1' => 0.75 + self::seeded($id * 2.2) * 0.3,
            'amp2' => 0.15 + self::seeded($id * 3.3) * 0.15,
            'phase1' => self::seeded($id * 4.4) * M_PI * 2,
            'phase2' => self::seeded($id * 5.5) * M_PI * 2,
        ];
    }

    /** Tinggi air (meter, 2 desimal) pada waktu tertentu. */
    public function heightAt(int $locationId, CarbonInterface $at): float
    {
        $p = $this->params($locationId);
        $hours = $at->getTimestamp() / 3600;

        $wave1 = $p['amp1'] * sin((2 * M_PI * $hours) / self::PERIOD_SEMI_DIURNAL_H + $p['phase1']);
        $wave2 = $p['amp2'] * sin((2 * M_PI * $hours) / self::PERIOD_DIURNAL_H + $p['phase2']);

        return max(0.0, self::round(($p['baseline'] + $wave1 + $wave2) * 100) / 100);
    }

    /** @return array{windSpeedKmh: int, waveHeightM: float} */
    public function weatherAt(int $locationId, CarbonInterface $at): array
    {
        $id = $locationId > 0 ? $locationId : 1;
        $hours = $at->getTimestamp() / 3600;
        $windNoise = self::seeded($id * 7.1 + floor($hours) * 0.37);
        $waveNoise = self::seeded($id * 9.3 + floor($hours) * 0.53);

        return [
            'windSpeedKmh' => (int) self::round(12 + $windNoise * 28),
            'waveHeightM' => self::round((0.5 + $waveNoise * 2.0) * 10) / 10,
        ];
    }

    /**
     * Status saat ini untuk Beranda.
     *
     * @return array{
     *   locationId: int, waterLevel: float, level: TideLevel, trend: string, trendChange: float,
     *   windSpeedKmh: int, waveHeightM: float, nextHighTideAt: ?CarbonImmutable,
     *   nextHighTideM: ?float, recordedAt: CarbonImmutable
     * }
     */
    public function status(int $locationId, ?CarbonInterface $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now())->toImmutable();
        $current = $this->heightAt($locationId, $now);
        $before = $this->heightAt($locationId, $now->subHour());
        $diff = self::round(($current - $before) * 100) / 100;

        $trend = 'STABIL';
        if ($diff > 0.02) {
            $trend = 'NAIK';
        } elseif ($diff < -0.02) {
            $trend = 'TURUN';
        }

        $nextHigh = null;
        foreach ($this->series($locationId, $now, 24) as $point) {
            if ($point['type'] === 'PASANG' && ($nextHigh === null || $point['height'] > $nextHigh['height'])) {
                $nextHigh = $point;
            }
        }

        $weather = $this->weatherAt($locationId, $now);

        return [
            'locationId' => $locationId,
            'waterLevel' => $current,
            'level' => TideLevel::fromHeight($current),
            'trend' => $trend,
            'trendChange' => $diff,
            'windSpeedKmh' => $weather['windSpeedKmh'],
            'waveHeightM' => $weather['waveHeightM'],
            'nextHighTideAt' => $nextHigh['at'] ?? null,
            'nextHighTideM' => $nextHigh['height'] ?? null,
            'recordedAt' => $now,
        ];
    }

    /**
     * Deret titik per jam dari $start sampai $hoursAhead jam ke depan.
     * Puncak lokal ditandai PASANG / SURUT.
     *
     * @return list<array{at: CarbonImmutable, height: float, type: string}>
     */
    public function series(int $locationId, CarbonInterface $start, int $hoursAhead): array
    {
        $start = $start->toImmutable();
        $points = [];
        for ($h = -1; $h <= $hoursAhead + 1; $h++) {
            $at = $start->addHours($h);
            $points[] = ['at' => $at, 'height' => $this->heightAt($locationId, $at), 'h' => $h];
        }

        $result = [];
        for ($i = 1, $n = count($points) - 1; $i < $n; $i++) {
            $prev = $points[$i - 1];
            $cur = $points[$i];
            $next = $points[$i + 1];
            if ($cur['h'] < 0 || $cur['h'] > $hoursAhead) {
                continue;
            }

            $type = 'NORMAL';
            if ($cur['height'] >= $prev['height'] && $cur['height'] >= $next['height']) {
                $type = 'PASANG';
            } elseif ($cur['height'] <= $prev['height'] && $cur['height'] <= $next['height']) {
                $type = 'SURUT';
            }

            $result[] = ['at' => $cur['at'], 'height' => $cur['height'], 'type' => $type];
        }

        return $result;
    }

    /**
     * Prediksi 24 atau 48 jam.
     *
     * @return array{rangeHours: int, series: list<array{at: CarbonImmutable, height: float, type: string}>}
     */
    public function prediction(int $locationId, int $hours = 24, ?CarbonInterface $now = null): array
    {
        $hours = $hours === 48 ? 48 : 24;
        $now = ($now ?? CarbonImmutable::now())->toImmutable();

        return [
            'rangeHours' => $hours,
            'series' => $this->series($locationId, $now, $hours),
        ];
    }

    /**
     * Riwayat 7 hari terakhir (169 titik per jam) beserta statistiknya.
     *
     * @return array{series: list<array{at: CarbonImmutable, height: float}>, average: float,
     *   highest: array{value: float, at: CarbonImmutable}, lowest: array{value: float, at: CarbonImmutable}}
     */
    public function history(int $locationId, ?CarbonInterface $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now())->toImmutable();
        $start = $now->subDays(7);

        $series = [];
        for ($h = 0; $h <= 7 * 24; $h++) {
            $at = $start->addHours($h);
            $series[] = ['at' => $at, 'height' => $this->heightAt($locationId, $at)];
        }

        $sum = 0.0;
        $max = $series[0];
        $min = $series[0];
        foreach ($series as $p) {
            $sum += $p['height'];
            if ($p['height'] > $max['height']) {
                $max = $p;
            }
            if ($p['height'] < $min['height']) {
                $min = $p;
            }
        }

        return [
            'series' => $series,
            'average' => self::round(($sum / count($series)) * 100) / 100,
            'highest' => ['value' => $max['height'], 'at' => $max['at']],
            'lowest' => ['value' => $min['height'], 'at' => $min['at']],
        ];
    }
}
