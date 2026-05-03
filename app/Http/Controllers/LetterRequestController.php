<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterArchive;
use App\Models\LetterRequest;
use App\Services\LetterDocumentService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LetterRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('resident');

        $query = LetterRequest::query()
            ->with('resident')
            ->latest();

        if ($user->isWarga()) {
            abort_unless($user->resident_id, 403, 'Akun warga belum terhubung ke data penduduk.');
            $query->where('resident_id', $user->resident_id);
        }

        return view('letters.index', [
            'isWarga' => $user->isWarga(),
            'isRt' => $user->isRt(),
            'isRw' => $user->isRw(),
            'letterTypes' => LetterType::options(),
            'requirementsMap' => LetterType::requirementsMap(),
            'letterRequests' => $query->paginate(10),
        ]);
    }

    public function store(Request $request, WhatsAppService $whatsAppService): RedirectResponse
    {
        $user = $request->user()->loadMissing('resident');
        abort_unless($user->isWarga(), 403);
        abort_unless($user->resident_id, 403, 'Akun warga belum terhubung ke data penduduk.');

        $validated = $request->validate([
            'letter_type' => ['required', Rule::in(LetterType::values())],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        $letterType = LetterType::from($validated['letter_type']);
        $requirements = $letterType->requirements();
        $attributeNames = [];
        $rules = [];

        foreach ($requirements as $key => $label) {
            $rules["documents.{$key}"] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
            $attributeNames["documents.{$key}"] = $label;
        }

        $request->validate($rules, [], $attributeNames);

        $storedDocuments = [];

        foreach ($requirements as $key => $label) {
            $file = $request->file("documents.{$key}");
            $path = $file->store('letter-documents', 'local');

            $storedDocuments[] = [
                'key' => $key,
                'label' => $label,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        $letterRequest = LetterRequest::query()->create([
            'resident_id' => $user->resident_id,
            'reference_number' => $this->generateReferenceNumber(),
            'letter_type' => $validated['letter_type'],
            'purpose' => $validated['purpose'],
            'status' => LetterRequestStatus::PENDING_RT->value,
            'documents' => $storedDocuments,
        ]);

        try {
            $whatsAppService->notifyRTNewSubmission($letterRequest);
        } catch (\Throwable $e) {
            Log::warning('Notifikasi WA RT pengajuan baru gagal: '.$e->getMessage());
        }

        return to_route('letters.index')->with('success', 'Pengajuan surat berhasil dibuat.');
    }

    public function rtDecision(Request $request, LetterRequest $letterRequest, WhatsAppService $whatsAppService): RedirectResponse
    {
        abort_unless($request->user()->isRt(), 403);

        if ($letterRequest->status !== LetterRequestStatus::PENDING_RT->value) {
            return to_route('letters.index')->with('error', 'Pengajuan ini tidak sedang menunggu verifikasi RT.');
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:decision,reject'],
        ]);

        $isApproved = $validated['decision'] === 'approve';

        $letterRequest->update([
            'status' => $isApproved
                ? LetterRequestStatus::PENDING_RW->value
                : LetterRequestStatus::REJECTED_RT->value,
            'rt_notes' => $validated['notes'] ?? null,
            'rw_notes' => null,
            'issued_at' => null,
            'letter_number' => null,
            'generated_content' => null,
        ]);

        try {
            if ($isApproved) {
                $whatsAppService->notifyWargaDiterimaRT($letterRequest);
                $whatsAppService->notifyRWNewSubmission($letterRequest);
            } else {
                $whatsAppService->notifyWargaDitolakRT($letterRequest, $validated['notes'] ?? '-');
            }
        } catch (\Throwable $e) {
            Log::warning('Notifikasi WA keputusan RT gagal: '.$e->getMessage());
        }

        $message = $isApproved
            ? 'Pengajuan disetujui RT dan diteruskan ke RW.'
            : 'Pengajuan ditolak oleh RT.';

        return to_route('letters.index')->with('success', $message);
    }

    public function rwDecision(Request $request, LetterRequest $letterRequest, WhatsAppService $whatsAppService): RedirectResponse
    {
        abort_unless($request->user()->isRw(), 403);

        if ($letterRequest->status !== LetterRequestStatus::PENDING_RW->value) {
            return to_route('letters.index')->with('error', 'Pengajuan ini tidak sedang menunggu verifikasi RW.');
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:decision,reject'],
        ]);

        $isApproved = $validated['decision'] === 'approve';

        if ($isApproved) {
            try {
                DB::transaction(function () use ($request, $validated, $letterRequest): void {
                    $letterRequest->loadMissing('resident');
                    $letterRequest->status = LetterRequestStatus::COMPLETED->value;
                    $letterRequest->rw_notes = $validated['notes'] ?? null;
                    $letterRequest->issued_at = now();
                    $letterRequest->letter_number = $this->generateLetterNumber();

                    $documentService = app(LetterDocumentService::class);
                    $docxPublicPath = $documentService->generateDocx($letterRequest);
                    $letterRequest->generated_docx_path = $docxPublicPath;

                    $pdfPath = $documentService->generatePdfFromDocx($docxPublicPath);
                    $letterRequest->generated_pdf_path = $pdfPath;

                    $letterRequest->generated_content = '';
                    $letterRequest->save();

                    LetterArchive::query()->updateOrCreate(
                        ['letter_request_id' => $letterRequest->id],
                        [
                            'resident_id' => $letterRequest->resident_id,
                            'archived_by' => $request->user()->id,
                            'archive_number' => $this->generateArchiveNumber(),
                            'reference_number' => $letterRequest->reference_number,
                            'letter_number' => $letterRequest->letter_number,
                            'letter_type' => $letterRequest->letter_type,
                            'request_status' => $letterRequest->status,
                            'resident_nik' => $letterRequest->resident->nik,
                            'resident_name' => $letterRequest->resident->name,
                            'purpose' => $letterRequest->purpose,
                            'documents' => $letterRequest->documents,
                            'generated_content' => $letterRequest->generated_content ?? '',
                            'generated_pdf_path' => $letterRequest->generated_pdf_path,
                            'generated_docx_path' => $letterRequest->generated_docx_path ?? null,
                            'issued_at' => $letterRequest->issued_at,
                            'archived_at' => now(),
                        ]
                    );
                });

                try {
                    $letterRequest->refresh();
                    $whatsAppService->notifyWargaDiterimaRW($letterRequest, (string) $letterRequest->generated_pdf_path);
                } catch (\Throwable $e) {
                    Log::warning('Notifikasi WA keputusan RW diterima gagal: '.$e->getMessage());
                }
            } catch (\Throwable $e) {
                Log::error('RW approval transaction failed: '.$e->getMessage());

                return to_route('letters.index')->with('error', 'Pengajuan tidak bisa diarsipkan: '.$e->getMessage());
            }
        } else {
            $letterRequest->update([
                'status' => LetterRequestStatus::REJECTED_RW->value,
                'rw_notes' => $validated['notes'] ?? null,
                'issued_at' => null,
                'letter_number' => null,
                'generated_content' => null,
                'generated_pdf_path' => null,
                'generated_docx_path' => null,
            ]);

            try {
                $whatsAppService->notifyWargaDitolakRW($letterRequest, $validated['notes'] ?? '-');
            } catch (\Throwable $e) {
                Log::warning('Notifikasi WA keputusan RW ditolak gagal: '.$e->getMessage());
            }
        }

        $message = $isApproved
            ? 'Pengajuan disetujui RW. Surat dan PDF berhasil dibuat.'
            : 'Pengajuan ditolak oleh RW.';

        return to_route('letters.index')->with('success', $message);
    }

    public function show(Request $request, LetterRequest $letterRequest): View
    {
        abort_unless($letterRequest->status === LetterRequestStatus::COMPLETED->value, 404);
        $letterRequest->loadMissing('resident');

        $this->authorizeRequestAccess($request, $letterRequest);

        return view('letters.show', [
            'letterRequest' => $letterRequest,
        ]);
    }

    public function cancel(Request $request, LetterRequest $letterRequest): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isWarga(), 403);

        $letterRequest->loadMissing('resident');
        $this->authorizeRequestAccess($request, $letterRequest);

        $cancellableStatuses = [
            LetterRequestStatus::PENDING_RT->value,
            LetterRequestStatus::PENDING_RW->value,
        ];

        if (! in_array($letterRequest->status, $cancellableStatuses, true)) {
            return to_route('letters.index')->with('error', 'Pengajuan ini tidak bisa dibatalkan.');
        }

        $documentPaths = collect($letterRequest->documents ?? [])
            ->pluck('path')
            ->filter()
            ->all();

        if ($documentPaths !== []) {
            Storage::disk('local')->delete($documentPaths);
        }

        $letterRequest->delete();

        return to_route('letters.index')->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    public function downloadDocument(Request $request, LetterRequest $letterRequest, string $key)
    {
        $letterRequest->loadMissing('resident');
        $this->authorizeRequestAccess($request, $letterRequest);

        $document = collect($letterRequest->documents ?? [])
            ->firstWhere('key', $key);

        abort_unless($document, 404, 'Dokumen tidak ditemukan.');
        abort_unless(Storage::disk('local')->exists($document['path']), 404, 'File tidak tersedia.');

        return response()->file(Storage::disk('local')->path($document['path']));
    }

    public function downloadPdf(Request $request, LetterRequest $letterRequest)
    {
        $letterRequest->loadMissing('resident');
        $this->authorizeRequestAccess($request, $letterRequest);
        
        abort_unless($letterRequest->generated_pdf_path, 404, 'File PDF tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($letterRequest->generated_pdf_path), 404, 'File tidak ditemukan.');

        return response()->file(
            Storage::disk('public')->path($letterRequest->generated_pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadDocx(Request $request, LetterRequest $letterRequest)
    {
        $letterRequest->loadMissing('resident');
        $this->authorizeRequestAccess($request, $letterRequest);

        abort_unless($letterRequest->generated_docx_path, 404, 'File DOCX tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($letterRequest->generated_docx_path), 404, 'File tidak ditemukan.');

        return response()->download(Storage::disk('public')->path($letterRequest->generated_docx_path), basename($letterRequest->generated_docx_path));
    }

    private function authorizeRequestAccess(Request $request, LetterRequest $letterRequest): void
    {
        $user = $request->user();

        if ($user->isWarga() && $letterRequest->resident_id !== $user->resident_id) {
            abort(403, 'Anda tidak memiliki akses ke data pengajuan ini.');
        }
    }

    private function generateReferenceNumber(): string
    {
        do {
            $candidate = 'REQ-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (LetterRequest::query()->where('reference_number', $candidate)->exists());

        return $candidate;
    }

    private function generateLetterNumber(): string
    {
        do {
            $candidate = '470/'.now()->format('m').'/RT-RW/'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT).'/'.now()->format('Y');
        } while (LetterRequest::query()->where('letter_number', $candidate)->exists());

        return $candidate;
    }

    private function generateArchiveNumber(): string
    {
        do {
            $candidate = 'ARS-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (LetterArchive::query()->where('archive_number', $candidate)->exists());

        return $candidate;
    }
}
