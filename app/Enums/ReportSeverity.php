<?php

namespace App\Enums;

enum ReportSeverity: string
{
    case RENDAH = 'RENDAH';
    case SEDANG = 'SEDANG';
    case TINGGI = 'TINGGI';

    public function label(): string
    {
        return match ($this) {
            self::RENDAH => 'Rendah',
            self::SEDANG => 'Sedang',
            self::TINGGI => 'Tinggi',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::RENDAH => 'bg-teal-100 text-teal-500',
            self::SEDANG => 'bg-amber-100 text-amber-500',
            self::TINGGI => 'bg-red-100 text-danger',
        };
    }

    /** Tingkat peringatan yang dikirim ke warga sekitar, null = tidak perlu peringatan. */
    public function alertLevel(): ?TideLevel
    {
        return match ($this) {
            self::RENDAH => null,
            self::SEDANG => TideLevel::WASPADA,
            self::TINGGI => TideLevel::BAHAYA,
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
