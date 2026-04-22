<?php

namespace App\Enums;

enum UserRole: string
{
    case WARGA = 'warga';
    case RT = 'pengurus_rt';
    case RW = 'pengurus_rw';

    public function label(): string
    {
        return match ($this) {
            self::WARGA => 'Warga',
            self::RT => 'Pengurus RT',
            self::RW => 'Pengurus RW',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
