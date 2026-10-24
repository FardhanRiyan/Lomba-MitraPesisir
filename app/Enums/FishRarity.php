<?php

namespace App\Enums;

enum FishRarity: string
{
    case UMUM = 'UMUM';
    case CUKUP_UMUM = 'CUKUP_UMUM';
    case JARANG = 'JARANG';
    case SANGAT_JARANG = 'SANGAT_JARANG';

    public function label(): string
    {
        return match ($this) {
            self::UMUM => 'Umum',
            self::CUKUP_UMUM => 'Cukup umum',
            self::JARANG => 'Jarang',
            self::SANGAT_JARANG => 'Sangat jarang',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::UMUM => 'bg-green-100 text-green-700',
            self::CUKUP_UMUM => 'bg-blue-100 text-blue-600',
            self::JARANG => 'bg-yellow-100 text-yellow-700',
            self::SANGAT_JARANG => 'bg-red-100 text-danger',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
