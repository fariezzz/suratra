<?php

namespace App\Enums;

enum LetterRequestStatus: string
{
    case PENDING_RT = 'menunggu_verifikasi_rt';
    case PENDING_RW = 'menunggu_verifikasi_rw';
    case REJECTED_RT = 'ditolak_rt';
    case REJECTED_RW = 'ditolak_rw';
    case COMPLETED = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_RT => 'Menunggu Verifikasi RT',
            self::PENDING_RW => 'Menunggu Verifikasi RW',
            self::REJECTED_RT => 'Ditolak RT',
            self::REJECTED_RW => 'Ditolak RW',
            self::COMPLETED => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING_RT => 'text-bg-warning',
            self::PENDING_RW => 'text-bg-info',
            self::REJECTED_RT, self::REJECTED_RW => 'text-bg-danger',
            self::COMPLETED => 'text-bg-success',
        };
    }
}
