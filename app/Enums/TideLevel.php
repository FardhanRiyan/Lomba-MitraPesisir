<?php

namespace App\Enums;

/**
 * Tingkat kondisi air laut. Ambang: < 1,5 m Aman · 1,5–1,99 m Waspada · ≥ 2,0 m Bahaya.
 */
enum TideLevel: string
{
    case AMAN = 'AMAN';
    case WASPADA = 'WASPADA';
    case BAHAYA = 'BAHAYA';

    public const WASPADA_FROM = 1.5;
    public const BAHAYA_FROM = 2.0;

    public static function fromHeight(float $height): self
    {
        if ($height >= self::BAHAYA_FROM) {
            return self::BAHAYA;
        }
        if ($height >= self::WASPADA_FROM) {
            return self::WASPADA;
        }

        return self::AMAN;
    }

    public function label(): string
    {
        return match ($this) {
            self::AMAN => 'Aman',
            self::WASPADA => 'Waspada',
            self::BAHAYA => 'Bahaya',
        };
    }

    public function message(): string
    {
        return match ($this) {
            self::AMAN => 'Kondisi air laut normal, aman untuk beraktivitas seperti biasa.',
            self::WASPADA => 'Perubahan air laut cukup signifikan, tetap perhatikan kondisi sekitar.',
            self::BAHAYA => 'Ketinggian air berisiko tinggi, disarankan tidak melaut sampai kondisi mereda.',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AMAN => 'bg-teal-100 text-teal-500',
            self::WASPADA => 'bg-amber-100 text-amber-500',
            self::BAHAYA => 'bg-red-100 text-danger',
        };
    }

    public function ringClass(): string
    {
        return match ($this) {
            self::AMAN => 'text-teal-500',
            self::WASPADA => 'text-amber-500',
            self::BAHAYA => 'text-danger',
        };
    }

    public function bannerClass(): string
    {
        return match ($this) {
            self::AMAN => 'bg-teal-100 text-teal-500',
            self::WASPADA => 'bg-amber-100 text-amber-500',
            self::BAHAYA => 'bg-red-100 text-danger',
        };
    }

    /** Hanya Waspada/Bahaya yang memicu peringatan. */
    public function needsAlert(): bool
    {
        return $this !== self::AMAN;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
