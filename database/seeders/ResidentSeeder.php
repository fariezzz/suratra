<?php

namespace Database\Seeders;

use App\Models\Resident;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $residents = [
            [
                'nik' => '3206010101900001',
                'name' => 'Budi Santoso',
                'gender' => 'L',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1990-01-01',
                'ktp_address' => 'Jl. Melati No. 12, Kel. Sukamaju',
                'address' => 'Jl. Melati No. 12, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567801',
                'occupation' => 'Karyawan Swasta',
            ],
            [
                'nik' => '3206011502920002',
                'name' => 'Siti Rahmawati',
                'gender' => 'P',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1992-02-15',
                'ktp_address' => 'Jl. Mawar No. 05, Kel. Sukamaju',
                'address' => 'Jl. Mawar No. 05, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567802',
                'occupation' => 'Wiraswasta',
            ],
            [
                'nik' => '3206010303850003',
                'name' => 'Dedi Kurniawan',
                'gender' => 'L',
                'birth_place' => 'Bandung',
                'birth_date' => '1985-03-03',
                'ktp_address' => 'Jl. Kenanga No. 14, Kel. Sukamaju',
                'address' => 'Jl. Kenanga No. 14, Kel. Sukamaju',
                'resident_status' => 'pendatang',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567803',
                'occupation' => 'Guru',
            ],
            [
                'nik' => '3206012207880004',
                'name' => 'Rina Marlina',
                'gender' => 'P',
                'birth_place' => 'Ciamis',
                'birth_date' => '1988-07-22',
                'ktp_address' => 'Jl. Anggrek No. 08, Kel. Sukamaju',
                'address' => 'Jl. Anggrek No. 08, Kel. Sukamaju',
                'resident_status' => 'pendatang',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567804',
                'occupation' => 'Ibu Rumah Tangga',
            ],
            [
                'nik' => '3206011009950005',
                'name' => 'Fajar Nugraha',
                'gender' => 'L',
                'birth_place' => 'Garut',
                'birth_date' => '1995-09-10',
                'ktp_address' => 'Jl. Dahlia No. 22, Kel. Sukamaju',
                'address' => 'Jl. Dahlia No. 22, Kel. Sukamaju',
                'resident_status' => 'pendatang',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567805',
                'occupation' => 'Mahasiswa',
            ],
            [
                'nik' => '3206011704960006',
                'name' => 'Nina Aisyah',
                'gender' => 'P',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1996-04-17',
                'ktp_address' => 'Jl. Cempaka No. 03, Kel. Sukamaju',
                'address' => 'Jl. Cempaka No. 03, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '003',
                'phone' => '081234567806',
                'occupation' => 'Perawat',
            ],
            [
                'nik' => '3206012801810007',
                'name' => 'Agus Priyanto',
                'gender' => 'L',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1981-01-28',
                'ktp_address' => 'Jl. Flamboyan No. 19, Kel. Sukamaju',
                'address' => 'Jl. Flamboyan No. 19, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '004',
                'phone' => '081234567807',
                'occupation' => 'Pedagang',
            ],
            [
                'nik' => '3206010507900008',
                'name' => 'Yuni Kartika',
                'gender' => 'P',
                'birth_place' => 'Banjar',
                'birth_date' => '1990-07-05',
                'ktp_address' => 'Jl. Teratai No. 09, Kel. Sukamaju',
                'address' => 'Jl. Teratai No. 09, Kel. Sukamaju',
                'resident_status' => 'pendatang',
                'rt' => '009',
                'rw' => '004',
                'phone' => '081234567808',
                'occupation' => 'Pegawai Negeri',
            ],
            [
                'nik' => '3206010904870009',
                'name' => 'Rudi Hartono',
                'gender' => 'L',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1987-04-09',
                'ktp_address' => 'Jl. Kamboja No. 11, Kel. Sukamaju',
                'address' => 'Jl. Kamboja No. 11, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '004',
                'phone' => '081234567809',
                'occupation' => 'Teknisi',
            ],
            [
                'nik' => '3206011210930010',
                'name' => 'Maya Permatasari',
                'gender' => 'P',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1993-10-12',
                'ktp_address' => 'Jl. Bougenville No. 18, Kel. Sukamaju',
                'address' => 'Jl. Bougenville No. 18, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '004',
                'phone' => '081234567810',
                'occupation' => 'Desainer Grafis',
            ],
            [
                'nik' => '3206013006000011',
                'name' => 'Irwan Syahputra',
                'gender' => 'L',
                'birth_place' => 'Cirebon',
                'birth_date' => '2000-06-30',
                'ktp_address' => 'Jl. Pahlawan No. 01, Kel. Sukamaju',
                'address' => 'Jl. Pahlawan No. 01, Kel. Sukamaju',
                'resident_status' => 'pendatang',
                'rt' => '009',
                'rw' => '005',
                'phone' => '081234567811',
                'occupation' => 'Freelancer',
            ],
            [
                'nik' => '3206011811990012',
                'name' => 'Lia Nurhasanah',
                'gender' => 'P',
                'birth_place' => 'Tasikmalaya',
                'birth_date' => '1999-11-18',
                'ktp_address' => 'Jl. Merpati No. 04, Kel. Sukamaju',
                'address' => 'Jl. Merpati No. 04, Kel. Sukamaju',
                'resident_status' => 'warga_asli',
                'rt' => '009',
                'rw' => '005',
                'phone' => '081234567812',
                'occupation' => 'Admin Online Shop',
            ],
        ];

        $rtOptions = ['001', '002'];

        $rows = array_map(
            static function (array $resident) use ($rtOptions, $now): array {
                return [
                    ...$resident,
                    'rt' => $rtOptions[array_rand($rtOptions)],
                    'rw' => '009',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            },
            $residents
        );

        Resident::query()->upsert(
            $rows,
            ['nik'],
            ['name', 'gender', 'birth_place', 'birth_date', 'ktp_address', 'address', 'resident_status', 'rt', 'rw', 'phone', 'occupation', 'updated_at']
        );
    }
}
