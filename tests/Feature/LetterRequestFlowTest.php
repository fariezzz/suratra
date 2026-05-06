<?php

namespace Tests\Feature;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterArchive;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;
use App\Services\LetterDocumentService;
use App\Services\WhatsAppService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class LetterRequestFlowTest extends TestCase
{
    public function test_warga_can_create_letter_request_with_required_documents(): void
    {
        Storage::fake('local');

        $resident = Resident::factory()->create();
        $user = User::factory()->linkedToResident($resident)->create();

        $waService = Mockery::mock(WhatsAppService::class);
        $waService->shouldReceive('notifyRTNewSubmission')->once()->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $waService);

        $this->actingAs($user)
            ->post('/pengajuan-surat', [
                'letter_type' => LetterType::GENERAL->value,
                'purpose' => 'Untuk keperluan administrasi',
                'documents' => [
                    'ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
                    'kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('letter_requests', [
            'resident_id' => $resident->id,
            'letter_type' => LetterType::GENERAL->value,
            'status' => LetterRequestStatus::PENDING_RT->value,
        ]);
    }

    public function test_warga_can_cancel_pending_request_and_remove_documents(): void
    {
        Storage::fake('local');

        $resident = Resident::factory()->create();
        $user = User::factory()->linkedToResident($resident)->create();

        Storage::disk('local')->put('letter-documents/ktp.pdf', 'content');

        $request = LetterRequest::factory()->for($resident)->create([
            'documents' => [[
                'key' => 'ktp',
                'label' => 'Scan KTP',
                'path' => 'letter-documents/ktp.pdf',
                'original_name' => 'ktp.pdf',
                'mime_type' => 'application/pdf',
                'size' => 100,
            ]],
            'status' => LetterRequestStatus::PENDING_RT->value,
        ]);

        $this->actingAs($user)
            ->delete('/pengajuan-surat/'.$request->id)
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('letter_requests', ['id' => $request->id]);
        $this->assertFalse(Storage::disk('local')->exists('letter-documents/ktp.pdf'));
    }

    public function test_rt_can_approve_and_reject_requests(): void
    {
        $resident = Resident::factory()->rt('001')->create();
        $request = LetterRequest::factory()->for($resident)->create([
            'status' => LetterRequestStatus::PENDING_RT->value,
        ]);

        $rtUser = User::factory()->rt('001')->create();
        $waService = Mockery::mock(WhatsAppService::class);
        $waService->shouldReceive('notifyWargaDiterimaRT')->once()->andReturnTrue();
        $waService->shouldReceive('notifyRWNewSubmission')->once()->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $waService);

        $this->actingAs($rtUser)
            ->post('/pengajuan-surat/'.$request->id.'/rt', [
                'decision' => 'approve',
            ])
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('letter_requests', [
            'id' => $request->id,
            'status' => LetterRequestStatus::PENDING_RW->value,
        ]);

        $rejected = LetterRequest::factory()->for($resident)->create([
            'status' => LetterRequestStatus::PENDING_RT->value,
        ]);

        $waServiceReject = Mockery::mock(WhatsAppService::class);
        $waServiceReject->shouldReceive('notifyWargaDitolakRT')->once()->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $waServiceReject);

        $this->actingAs($rtUser)
            ->post('/pengajuan-surat/'.$rejected->id.'/rt', [
                'decision' => 'reject',
                'notes' => 'Data belum lengkap',
            ])
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('letter_requests', [
            'id' => $rejected->id,
            'status' => LetterRequestStatus::REJECTED_RT->value,
            'rt_notes' => 'Data belum lengkap',
        ]);
    }

    public function test_rw_can_complete_or_reject_requests(): void
    {
        Storage::fake('public');

        $resident = Resident::factory()->create();
        $request = LetterRequest::factory()->for($resident)->pendingRw()->create([
            'status' => LetterRequestStatus::PENDING_RW->value,
        ]);

        $rwUser = User::factory()->rw()->create();

        $documentService = Mockery::mock(LetterDocumentService::class);
        $documentService->shouldReceive('generateDocx')->once()->andReturn('generated-letters/test.docx');
        $documentService->shouldReceive('generatePdfFromDocx')->once()->andReturn('generated-letters/test.pdf');
        $this->app->instance(LetterDocumentService::class, $documentService);

        $waService = Mockery::mock(WhatsAppService::class);
        $waService->shouldReceive('notifyWargaDiterimaRW')->once()->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $waService);

        $this->actingAs($rwUser)
            ->post('/pengajuan-surat/'.$request->id.'/rw', [
                'decision' => 'approve',
            ])
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('letter_requests', [
            'id' => $request->id,
            'status' => LetterRequestStatus::COMPLETED->value,
            'generated_pdf_path' => 'generated-letters/test.pdf',
        ]);
        $this->assertDatabaseHas('letter_archives', [
            'letter_request_id' => $request->id,
            'request_status' => LetterRequestStatus::COMPLETED->value,
        ]);

        $rejected = LetterRequest::factory()->for($resident)->pendingRw()->create([
            'status' => LetterRequestStatus::PENDING_RW->value,
        ]);

        $waServiceReject = Mockery::mock(WhatsAppService::class);
        $waServiceReject->shouldReceive('notifyWargaDitolakRW')->once()->andReturnTrue();
        $this->app->instance(WhatsAppService::class, $waServiceReject);

        $this->actingAs($rwUser)
            ->post('/pengajuan-surat/'.$rejected->id.'/rw', [
                'decision' => 'reject',
                'notes' => 'Perlu revisi',
            ])
            ->assertRedirect(route('letters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('letter_requests', [
            'id' => $rejected->id,
            'status' => LetterRequestStatus::REJECTED_RW->value,
            'rw_notes' => 'Perlu revisi',
        ]);
    }

    public function test_completed_request_can_be_viewed_and_downloaded_by_owner(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('generated-letters/test.pdf', 'pdf');
        Storage::disk('public')->put('generated-letters/test.docx', 'docx');

        $resident = Resident::factory()->create();
        $user = User::factory()->linkedToResident($resident)->create();
        $request = LetterRequest::factory()->for($resident)->completed()->create([
            'generated_pdf_path' => 'generated-letters/test.pdf',
            'generated_docx_path' => 'generated-letters/test.docx',
        ]);

        $this->actingAs($user)
            ->get('/pengajuan-surat/'.$request->id.'/surat')
            ->assertOk()
            ->assertViewIs('letters.show');

        $this->actingAs($user)
            ->get('/pengajuan-surat/'.$request->id.'/pdf')
            ->assertOk();

        $this->actingAs($user)
            ->get('/pengajuan-surat/'.$request->id.'/docx')
            ->assertOk();
    }
}