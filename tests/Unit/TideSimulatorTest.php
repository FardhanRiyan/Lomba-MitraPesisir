<?php

namespace Tests\Unit;

use App\Enums\TideLevel;
use App\Services\TideSimulator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class TideSimulatorTest extends TestCase
{
    private TideSimulator $tide;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        $this->tide = new TideSimulator;
        $this->now = CarbonImmutable::createFromTimestampUTC(1760000000);
    }

    public function test_ketinggian_deterministik_untuk_lokasi_dan_waktu_sama(): void
    {
        $this->assertSame($this->tide->heightAt(1, $this->now), $this->tide->heightAt(1, $this->now));
    }

    public function test_lokasi_berbeda_memberi_pola_berbeda(): void
    {
        $this->assertNotSame($this->tide->heightAt(1, $this->now), $this->tide->heightAt(2, $this->now));
    }

    public function test_ambang_status(): void
    {
        $this->assertSame(TideLevel::AMAN, TideLevel::fromHeight(1.49));
        $this->assertSame(TideLevel::WASPADA, TideLevel::fromHeight(1.5));
        $this->assertSame(TideLevel::WASPADA, TideLevel::fromHeight(1.99));
        $this->assertSame(TideLevel::BAHAYA, TideLevel::fromHeight(2.0));
    }

    public function test_status_memuat_semua_bidang(): void
    {
        $s = $this->tide->status(1, $this->now);
        $this->assertSame(1, $s['locationId']);
        $this->assertContains($s['trend'], ['NAIK', 'TURUN', 'STABIL']);
        $this->assertSame(TideLevel::fromHeight($s['waterLevel']), $s['level']);
        $this->assertGreaterThanOrEqual(12, $s['windSpeedKmh']);
        $this->assertLessThanOrEqual(40, $s['windSpeedKmh']);
    }

    public function test_prediksi_24_dan_48_jam(): void
    {
        $this->assertCount(25, $this->tide->prediction(1, 24, $this->now)['series']);
        $this->assertCount(49, $this->tide->prediction(1, 48, $this->now)['series']);
        // Nilai selain 48 dianggap 24.
        $this->assertSame(24, $this->tide->prediction(1, 7, $this->now)['rangeHours']);
    }

    public function test_riwayat_7_hari(): void
    {
        $h = $this->tide->history(1, $this->now);
        $this->assertCount(169, $h['series']);
        $this->assertGreaterThanOrEqual($h['lowest']['value'], $h['average']);
        $this->assertLessThanOrEqual($h['highest']['value'], $h['average']);
    }
}
