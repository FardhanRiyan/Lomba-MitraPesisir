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
}
