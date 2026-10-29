<?php

namespace App\Enums;

enum ReportCategory: string
{
    case KONDISI_AIR = 'KONDISI_AIR';
    case CUACA_EKSTREM = 'CUACA_EKSTREM';
    case PENCEMARAN = 'PENCEMARAN';
    case ABRASI = 'ABRASI';
    case LAINNYA = 'LAINNYA';

    public function label(): string 
    {
        return match($this){
            self::KONDISI_AIR => 'Kondisi air laut',
            self::CUACA_EKSTREM => 'Cuaca ekstrem',
            self::PENCEMARAN => 'Pencemaran',
            self::ABRASI => 'Abrasi / kerusakan pantai',
            self::LAINNYA => 'Lainnya',
        };
    }

    public static function values(): array 
    {
        return array_column(self::cases(), 'value');
    }
}