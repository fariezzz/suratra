<?php

namespace Tests\Unit;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_letter_type_labels_and_requirements_are_mapped_correctly(): void
    {
        $this->assertSame('Surat Pengantar Umum', LetterType::GENERAL->label());
        $this->assertSame('Surat Keterangan Usaha', LetterType::BUSINESS->label());

        $options = LetterType::options();
        $this->assertArrayHasKey(LetterType::DOMICILE->value, $options);
        $this->assertSame('Surat Keterangan Domisili', $options[LetterType::DOMICILE->value]);

        $requirements = LetterType::DOMICILE->requirements();
        $this->assertArrayHasKey('bukti_tinggal', $requirements);
        $this->assertSame('Bukti Domisili (surat kontrak/rek listrik)', $requirements['bukti_tinggal']);
    }

    public function test_letter_request_status_labels_and_badges_are_mapped_correctly(): void
    {
        $this->assertSame('Menunggu Verifikasi RT', LetterRequestStatus::PENDING_RT->label());
        $this->assertSame('text-bg-success', LetterRequestStatus::COMPLETED->badgeClass());
        $this->assertSame('text-bg-danger', LetterRequestStatus::REJECTED_RW->badgeClass());
    }

    public function test_user_role_labels_are_available(): void
    {
        $this->assertSame('Warga', UserRole::WARGA->label());
        $this->assertSame('Pengurus RT', UserRole::RT->label());
        $this->assertSame('Pengurus RW', UserRole::RW->label());
    }
}