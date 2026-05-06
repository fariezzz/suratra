<?php

namespace Tests\Feature;

use App\Enums\LetterRequestStatus;
use App\Models\LetterArchive;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveFlowTest extends TestCase
{
    public function test_archive_index_can_be_filtered_by_search_and_letter_type(): void
    {
        $resident = Resident::factory()->create();
        $archive = LetterArchive::factory()->create([
            'resident_id' => $resident->id,
            'resident_nik' => $resident->nik,
            'resident_name' => $resident->name,
            'letter_type' => 'surat_pengantar_umum',
            'request_status' => LetterRequestStatus::COMPLETED->value,
            'reference_number' => 'REQ-20260506-0001',
        ]);

        $user = User::factory()->linkedToResident($resident)->create();

        $this->actingAs($user)
            ->get('/arsip-surat?q=REQ-20260506&letter_type=surat_pengantar_umum')
            ->assertOk()
            ->assertViewIs('archives.index')
            ->assertViewHas('selectedLetterType', 'surat_pengantar_umum');
    }

    public function test_warga_can_view_and_download_own_archive_only(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('generated-letters/archive.pdf', 'pdf');
        Storage::disk('public')->put('generated-letters/archive.docx', 'docx');

        $resident = Resident::factory()->create();
        $archive = LetterArchive::factory()->create([
            'resident_id' => $resident->id,
            'resident_nik' => $resident->nik,
            'resident_name' => $resident->name,
            'generated_pdf_path' => 'generated-letters/archive.pdf',
            'generated_docx_path' => 'generated-letters/archive.docx',
        ]);

        $owner = User::factory()->linkedToResident($resident)->create();
        $other = User::factory()->linkedToResident(Resident::factory()->create())->create();

        $this->actingAs($owner)
            ->get('/arsip-surat/'.$archive->id)
            ->assertOk()
            ->assertViewIs('archives.show');

        $this->actingAs($owner)
            ->get('/arsip-surat/'.$archive->id.'/pdf')
            ->assertOk();

        $this->actingAs($owner)
            ->get('/arsip-surat/'.$archive->id.'/docx')
            ->assertOk();

        $this->actingAs($other)
            ->get('/arsip-surat/'.$archive->id)
            ->assertForbidden();
    }
}