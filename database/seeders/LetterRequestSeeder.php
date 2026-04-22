<?php

namespace Database\Seeders;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterRequest;
use App\Models\Resident;
use Illuminate\Database\Seeder;

class LetterRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residentByNik = Resident::query()->pluck('id', 'nik');

        $sampleRequests = [
            [
                'reference_number' => 'REQ-20260416-0001',
                'resident_nik' => '3206010101900001',
                'letter_type' => LetterType::DOMICILE->value,
                'purpose' => 'Melengkapi administrasi kontrak kerja',
                'status' => LetterRequestStatus::PENDING_RT->value,
            ],
            [
                'reference_number' => 'REQ-20260416-0002',
                'resident_nik' => '3206011502920002',
                'letter_type' => LetterType::SKCK->value,
                'purpose' => 'Persyaratan pendaftaran pekerjaan',
                'status' => LetterRequestStatus::PENDING_RW->value,
                'rt_notes' => 'Data sudah sesuai dan dilanjutkan ke RW.',
            ],
            [
                'reference_number' => 'REQ-20260416-0003',
                'resident_nik' => '3206010303850003',
                'letter_type' => LetterType::BUSINESS->value,
                'purpose' => 'Pengajuan legalitas usaha rumahan',
                'status' => LetterRequestStatus::COMPLETED->value,
                'rt_notes' => 'Dokumen usaha lengkap.',
                'rw_notes' => 'Disetujui RW.',
                'letter_number' => '470/04/RT-RW/001/2026',
                'issued_at' => now()->subDays(3),
            ],
            [
                'reference_number' => 'REQ-20260416-0004',
                'resident_nik' => '3206012207880004',
                'letter_type' => LetterType::GENERAL->value,
                'purpose' => 'Permohonan rekomendasi administrasi bank',
                'status' => LetterRequestStatus::REJECTED_RT->value,
                'rt_notes' => 'Dokumen KTP belum terunggah.',
            ],
            [
                'reference_number' => 'REQ-20260416-0005',
                'resident_nik' => '3206011009950005',
                'letter_type' => LetterType::DOMICILE->value,
                'purpose' => 'Syarat pengajuan beasiswa',
                'status' => LetterRequestStatus::REJECTED_RW->value,
                'rt_notes' => 'Sudah diverifikasi RT.',
                'rw_notes' => 'Alamat domisili perlu pembaruan data.',
            ],
            [
                'reference_number' => 'REQ-20260416-0006',
                'resident_nik' => '3206011704960006',
                'letter_type' => LetterType::SKCK->value,
                'purpose' => 'Perpanjangan dokumen kerja',
                'status' => LetterRequestStatus::COMPLETED->value,
                'rt_notes' => 'Sudah sesuai.',
                'rw_notes' => 'Disetujui.',
                'letter_number' => '470/04/RT-RW/002/2026',
                'issued_at' => now()->subDay(),
            ],
        ];

        LetterRequest::query()->delete();

        foreach ($sampleRequests as $requestData) {
            $residentId = $residentByNik[$requestData['resident_nik']] ?? null;

            if (! $residentId) {
                continue;
            }

            $resident = Resident::query()->find($residentId);
            $isCompleted = ($requestData['status'] ?? null) === LetterRequestStatus::COMPLETED->value;

            LetterRequest::query()->create([
                'resident_id' => $residentId,
                'reference_number' => $requestData['reference_number'],
                'letter_type' => $requestData['letter_type'],
                'purpose' => $requestData['purpose'],
                'status' => $requestData['status'],
                'rt_notes' => $requestData['rt_notes'] ?? null,
                'rw_notes' => $requestData['rw_notes'] ?? null,
                'documents' => [],
                'letter_number' => $requestData['letter_number'] ?? null,
                'generated_content' => $isCompleted && $resident
                    ? $this->dummyGeneratedContent($resident->name, $requestData['letter_number'], $requestData['purpose'])
                    : null,
                'issued_at' => $requestData['issued_at'] ?? null,
            ]);
        }
    }

    private function dummyGeneratedContent(string $name, ?string $letterNumber, string $purpose): string
    {
        return sprintf(
            '<div class="text-center mb-4"><h5 class="mb-1">SURAT KETERANGAN</h5><p class="mb-0">Nomor: %s</p></div><p>Surat ini menerangkan bahwa <strong>%s</strong> adalah warga setempat untuk keperluan <strong>%s</strong>.</p><p class="mb-0 text-end mt-5"><strong>Pengurus RW</strong></p>',
            e((string) $letterNumber),
            e($name),
            e(strtolower($purpose))
        );
    }
}
