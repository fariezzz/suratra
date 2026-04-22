<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterArchive;
use App\Models\LetterRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
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

        LetterRequest::query()->create([
            'resident_id' => $user->resident_id,
            'reference_number' => $this->generateReferenceNumber(),
            'letter_type' => $validated['letter_type'],
            'purpose' => $validated['purpose'],
            'status' => LetterRequestStatus::PENDING_RT->value,
            'documents' => $storedDocuments,
        ]);

        return to_route('letters.index')->with('success', 'Pengajuan surat berhasil dibuat.');
    }

    public function rtDecision(Request $request, LetterRequest $letterRequest): RedirectResponse
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

        $message = $isApproved
            ? 'Pengajuan disetujui RT dan diteruskan ke RW.'
            : 'Pengajuan ditolak oleh RT.';

        return to_route('letters.index')->with('success', $message);
    }

    public function rwDecision(Request $request, LetterRequest $letterRequest): RedirectResponse
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
            $letterRequest->loadMissing('resident');
            $letterRequest->status = LetterRequestStatus::COMPLETED->value;
            $letterRequest->rw_notes = $validated['notes'] ?? null;
            $letterRequest->issued_at = now();
            $letterRequest->letter_number = $this->generateLetterNumber();
            $letterRequest->generated_content = $this->renderGeneratedLetter($letterRequest);
            $letterRequest->save();

            LetterArchive::query()->updateOrCreate(
                ['letter_request_id' => $letterRequest->id],
                [
                    'resident_id' => $letterRequest->resident_id,
                    'archived_by' => $request->user()->id,
                    'reference_number' => $letterRequest->reference_number,
                    'letter_number' => $letterRequest->letter_number,
                    'letter_type' => $letterRequest->letter_type,
                    'request_status' => $letterRequest->status,
                    'resident_nik' => $letterRequest->resident->nik,
                    'resident_name' => $letterRequest->resident->name,
                    'purpose' => $letterRequest->purpose,
                    'documents' => $letterRequest->documents,
                    'generated_content' => $letterRequest->generated_content,
                    'issued_at' => $letterRequest->issued_at,
                    'archived_at' => now(),
                ]
            );
        } else {
            $letterRequest->update([
                'status' => LetterRequestStatus::REJECTED_RW->value,
                'rw_notes' => $validated['notes'] ?? null,
                'issued_at' => null,
                'letter_number' => null,
                'generated_content' => null,
            ]);
        }

        $message = $isApproved
            ? 'Pengajuan disetujui RW. Surat otomatis berhasil dibuat.'
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

    private function renderGeneratedLetter(LetterRequest $letterRequest): string
    {
        $type = LetterType::from($letterRequest->letter_type);

        return view($type->templateView(), [
            'letterRequest' => $letterRequest,
        ])->render();
    }
}
