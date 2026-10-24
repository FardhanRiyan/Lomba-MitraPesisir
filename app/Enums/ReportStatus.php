<?php

namespace App\Enums;

enum ReportStatus: string
{
    case BARU = 'BARU';
    case DIPROSES = 'DIPROSES';
    case SELESAI = 'SELESAI';

    public function label(): string
    {
        return match ($this) {
            self::BARU => 'Baru',
            self::DIPROSES => 'Diproses',
            self::SELESAI => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BARU => 'bg-blue-100 text-blue-600',
            self::DIPROSES => 'bg-amber-100 text-amber-500',
            self::SELESAI => 'bg-teal-100 text-teal-500',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
