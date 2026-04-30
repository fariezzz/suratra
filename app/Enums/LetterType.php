<?php

namespace App\Enums;

enum LetterType: string
{
    case GENERAL = 'surat_pengantar_umum';
    case DOMICILE = 'surat_pengantar_domisili';
    case SKCK = 'surat_pengantar_skck';
    case BUSINESS = 'surat_keterangan_usaha';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'Surat Pengantar Umum',
            self::DOMICILE => 'Surat Pengantar Domisili',
            self::SKCK => 'Surat Pengantar SKCK',
            self::BUSINESS => 'Surat Keterangan Usaha',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function requirements(): array
    {
        return match ($this) {
            self::GENERAL => [
                'ktp' => 'Scan KTP',
                'kk' => 'Scan Kartu Keluarga',
            ],
            self::DOMICILE => [
                'ktp' => 'Scan KTP',
                'kk' => 'Scan Kartu Keluarga',
                'bukti_tinggal' => 'Bukti Domisili (surat kontrak/rek listrik)',
            ],
            self::SKCK => [
                'ktp' => 'Scan KTP',
                'kk' => 'Scan Kartu Keluarga',
                'pas_foto' => 'Pas Foto 3x4',
            ],
            self::BUSINESS => [
                'ktp' => 'Scan KTP',
                'foto_usaha' => 'Foto Tempat Usaha',
                'surat_pernyataan' => 'Surat Pernyataan Usaha',
            ],
        };
    }

    public static function requirementsMap(): array
    {
        $map = [];

        foreach (self::cases() as $case) {
            $map[$case->value] = $case->requirements();
        }

        return $map;
    }
}
