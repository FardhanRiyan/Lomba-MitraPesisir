<?php

namespace App\Enums;

/** Peran pengguna untuk RBAC. ADMIN tidak bisa dipilih saat mendaftar. */
enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case USER = 'USER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }

    /** Peran yang boleh dipilih sendiri oleh pengguna saat mendaftar. */
    public static function registrable(): array
    {
        return [self::USER];
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
