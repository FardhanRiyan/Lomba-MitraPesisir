<?php

namespace Tests\Unit;

use App\Services\TideSimulator;
use App\Support\ChartBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ChartBuilderTest extends TestCase
{
    public function test_penanda_waktu_sekarang_berada_di_sisi_kanan_grafik(): void
    {
        $at = CarbonImmutable::createFromTimestampUTC(1760000000);
        $series = (new TideSimulator)->prediction(1, 24, $at)['series'];
        $chart = ChartBuilder::prediction($series);

        $this->assertNotNull($chart);
        $this->assertSame($chart['points'][array_key_last($chart['points'])]['x'], $chart['nowX']);
        $this->assertSame($chart['points'][array_key_last($chart['points'])]['y'], $chart['nowY']);
        $this->assertGreaterThan($chart['points'][0]['x'], $chart['nowX']);
    }

    public function test_label_extreme_48_jam_tidak_saling_tumpang_tindih(): void
    {
        $at = CarbonImmutable::createFromTimestampUTC(1760000000);
        $series = (new TideSimulator)->prediction(1, 48, $at)['series'];
        $chart = ChartBuilder::prediction($series);
        $lanes = [];

        foreach ($chart['extremes'] as $label) {
            $width = mb_strlen($label['text']) * 5.5;
            $left = match ($label['anchor']) {
                'start' => $label['x'],
                'end' => $label['x'] - $width,
                default => $label['x'] - $width / 2,
            };
            $right = $left + $width;
            $lane = $label['labelY'];

            foreach ($lanes[$lane] ?? [] as [$previousLeft, $previousRight]) {
                $this->assertGreaterThanOrEqual($previousRight + 8, $left);
            }

            $lanes[$lane][] = [$left, $right];
            $this->assertLessThanOrEqual(62, $label['labelY']);
        }
    }
}
