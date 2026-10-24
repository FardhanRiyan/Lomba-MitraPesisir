<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Menyusun data geometri grafik SVG (path halus, titik puncak, label sumbu)
 * dari deret tinggi air. View hanya merender hasilnya — tanpa JavaScript.
 */
final class ChartBuilder
{
    private const W = 600;

    /** Kurva halus Catmull-Rom -> cubic bezier melalui deretan titik. */
    private static function smoothPath(array $pts): string
    {
        if (count($pts) < 2) {
            return '';
        }
        $f = static fn (float $v): string => sprintf('%.1F', $v);
        $d = 'M '.$f($pts[0]['x']).' '.$f($pts[0]['y']);
        for ($i = 0, $n = count($pts) - 1; $i < $n; $i++) {
            $p0 = $pts[$i - 1] ?? $pts[$i];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[$i + 2] ?? $p2;
            $d .= ' C '.$f($p1['x'] + ($p2['x'] - $p0['x']) / 6).' '.$f($p1['y'] + ($p2['y'] - $p0['y']) / 6)
                .', '.$f($p2['x'] - ($p3['x'] - $p1['x']) / 6).' '.$f($p2['y'] - ($p3['y'] - $p1['y']) / 6)
                .', '.$f($p2['x']).' '.$f($p2['y']);
        }

        return $d;
    }

    private static function anchor(float $x, float $left, float $right): string
    {
        if ($x < $left + 70) {
            return 'start';
        }
        if ($x > $right - 70) {
            return 'end';
        }

        return 'middle';
    }

    /**
     * Geometri umum: garis, area, fungsi skala.
     *
     * @param  list<array{at: CarbonImmutable, height: float}>  $series
     * @return array{points: list<array{x: float, y: float}>, line: string, area: string, baseY: float, padTop: float, w: int, h: int}|null
     */
    private static function base(array $series, int $h, array $pad, float $headroom): ?array
    {
        $n = count($series);
        if ($n < 2) {
            return null;
        }
        $heights = array_column($series, 'height');
        $yMax = max($heights) + $headroom;
        $innerW = self::W - $pad['l'] - $pad['r'];
        $innerH = $h - $pad['t'] - $pad['b'];

        $points = [];
        foreach ($series as $i => $p) {
            $points[] = [
                'x' => $pad['l'] + ($i / ($n - 1)) * $innerW,
                'y' => $pad['t'] + $innerH - (($p['height'] - 0) / ($yMax - 0)) * $innerH,
            ];
        }
        $baseY = (float) ($pad['t'] + $innerH);
        $line = self::smoothPath($points);
        $last = $points[$n - 1];
        $area = $line.sprintf(' L %.1F %.1F L %.1F %.1F Z', $last['x'], $baseY, $points[0]['x'], $baseY);

        return ['points' => $points, 'line' => $line, 'area' => $area, 'baseY' => $baseY, 'padTop' => (float) $pad['t'], 'w' => self::W, 'h' => $h];
    }

    /**
     * Grafik prediksi: area halus + puncak pasang/surut + garis "sekarang".
     *
     * @param  list<array{at: CarbonImmutable, height: float, type: string}>  $series
     */
    public static function prediction(array $series): ?array
    {
        // Reverse the forecast so the current observation sits at the right edge.
        $series = array_reverse($series);
        $pad = ['l' => 14, 'r' => 14, 't' => 34, 'b' => 38];
        $g = self::base($series, 300, $pad, 0.5);
        if ($g === null) {
            return null;
        }

        $right = self::W - $pad['r'];
        $extremes = [];
        $ticks = [];
        foreach ($series as $i => $p) {
            $x = $g['points'][$i]['x'];
            $y = $g['points'][$i]['y'];
            if ($p['type'] !== 'NORMAL') {
                $isPeak = $p['type'] === 'PASANG';
                $extremes[] = [
                    'x' => $x,
                    'y' => $y,
                    'labelY' => $isPeak ? $y - 10 : $y + 18,
                    'anchor' => self::anchor($x, $pad['l'], $right),
                    'text' => ($isPeak ? 'Pasang ' : 'Surut ').number_format($p['height'], 1, ',', '.').'m · '.Format::clock($p['at']),
                ];
            }
            if ($i % 6 === 0) {
                $ticks[] = ['x' => $x, 'label' => Format::clock($p['at']), 'anchor' => self::anchor($x, $pad['l'], $right)];
            }
        }

        return $g + [
            'variant' => 'prediction',
            'extremes' => $extremes,
            'ticks' => $ticks,
            'nowX' => $g['points'][count($g['points']) - 1]['x'],
            'nowY' => $g['points'][count($g['points']) - 1]['y'],
            'summary' => 'Grafik prediksi pasang surut, '.count($extremes).' puncak pasang dan surut.',
        ];
    }

    /**
     * Grafik riwayat 7 hari: gelombang + label hari + titik tertinggi/terendah.
     *
     * @param  list<array{at: CarbonImmutable, height: float}>  $series
     */
    public static function history(array $series): ?array
    {
        $pad = ['l' => 14, 'r' => 14, 't' => 14, 'b' => 34];
        $g = self::base($series, 190, $pad, 0.2);
        if ($g === null) {
            return null;
        }

        $dayTicks = [];
        $hi = 0;
        $lo = 0;
        foreach ($series as $i => $p) {
            if ($p['at']->hour === 12) {
                $dayTicks[] = ['x' => $g['points'][$i]['x'], 'label' => $p['at']->translatedFormat('D')];
            }
            if ($p['height'] > $series[$hi]['height']) {
                $hi = $i;
            }
            if ($p['height'] < $series[$lo]['height']) {
                $lo = $i;
            }
        }

        return $g + [
            'variant' => 'history',
            'dayTicks' => $dayTicks,
            'high' => $g['points'][$hi],
            'low' => $g['points'][$lo],
            'summary' => 'Grafik riwayat pasang surut 7 hari terakhir.',
        ];
    }
}
