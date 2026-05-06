<?php

namespace Tests\Unit;

use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.enabled' => true,
            'services.whatsapp.base_url' => 'http://whatsapp.test',
        ]);
    }

    public function test_send_message_posts_to_microservice(): void
    {
        Http::fake([
            'http://whatsapp.test/send-message' => Http::response(['ok' => true], 200),
        ]);

        $service = app(WhatsAppService::class);

        $this->assertTrue($service->sendMessage('08123456789', 'Halo'));

        Http::assertSent(function ($request): bool {
            return $request->url() === 'http://whatsapp.test/send-message'
                && $request['phone'] === '08123456789'
                && $request['message'] === 'Halo';
        });
    }

    public function test_send_document_resolves_public_storage_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'wa-test-');
        file_put_contents($tempFile, 'pdf-content');

        Http::fake([
            'http://whatsapp.test/send-document' => Http::response(['ok' => true], 200),
        ]);

        $service = app(WhatsAppService::class);

        $this->assertTrue($service->sendDocument('08123456789', 'Dokumen', $tempFile));

        Http::assertSent(function ($request) use ($tempFile): bool {
            return $request->url() === 'http://whatsapp.test/send-document'
                && $request['phone'] === '08123456789'
                && $request['message'] === 'Dokumen'
                && $request['filePath'] === $tempFile;
        });

        @unlink($tempFile);
    }

    public function test_notify_rt_new_submission_sends_message_to_matching_rt_only(): void
    {
        $resident = Resident::factory()->create(['rt' => '001', 'phone' => '081200000001']);
        $request = LetterRequest::factory()->for($resident)->type(LetterType::GENERAL)->create();

        User::factory()->rt('001')->linkedToResident(Resident::factory()->create(['phone' => '081200000010']))->create();
        User::factory()->rt('002')->linkedToResident(Resident::factory()->create(['phone' => '081200000020']))->create();

        $service = $this->partialMock(WhatsAppService::class, function ($mock): void {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with('082116375827', Mockery::on(fn (string $message): bool => str_contains($message, 'Pengajuan surat baru')))
                ->andReturnTrue();
        });

        $this->assertTrue($service->notifyRTNewSubmission($request));
    }

    public function test_notify_warga_approved_rw_uses_document_when_available(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('generated-letters/result.pdf', 'pdf');

        Http::fake([
            'http://whatsapp.test/send-document' => Http::response(['ok' => true], 200),
        ]);

        $resident = Resident::factory()->create(['phone' => '081233344455']);
        $request = LetterRequest::factory()->for($resident)->completed()->create([
            'letter_type' => LetterType::BUSINESS->value,
            'generated_pdf_path' => 'generated-letters/result.pdf',
        ]);

        $service = app(WhatsAppService::class);

        $this->assertTrue($service->notifyWargaDiterimaRW($request, 'generated-letters/result.pdf'));
        Http::assertSent(function ($request): bool {
            return $request->url() === 'http://whatsapp.test/send-document';
        });
    }
}