<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterArchive;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LetterArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('resident');
        $search = trim((string) $request->string('q'));
        $letterType = $request->string('letter_type')->toString();

        $query = LetterArchive::query()
            ->where('request_status', LetterRequestStatus::COMPLETED->value);

        if ($user->isWarga()) {
            abort_unless($user->resident_id, 403, 'Akun warga belum terhubung ke data penduduk.');
            $query->where('resident_id', $user->resident_id);
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('letter_number', 'like', "%{$search}%")
                    ->orWhere('resident_name', 'like', "%{$search}%")
                    ->orWhere('resident_nik', 'like', "%{$search}%");
            });
        }

        if ($letterType !== '') {
            $query->where('letter_type', $letterType);
        }

        return view('archives.index', [
            'archives' => $query->latest('archived_at')->paginate(10)->withQueryString(),
            'letterTypes' => LetterType::options(),
            'selectedLetterType' => $letterType,
            'search' => $search,
            'isWarga' => $user->isWarga(),
        ]);
    }

    public function show(Request $request, LetterArchive $letterArchive): View
    {
        $user = $request->user();

        if ($user->isWarga()) {
            abort_unless($user->resident_id && $letterArchive->resident_id === $user->resident_id, 403, 'Anda tidak memiliki akses ke arsip ini.');
        }

        return view('archives.show', [
            'letterArchive' => $letterArchive,
        ]);
    }

    public function downloadPdf(Request $request, LetterArchive $letterArchive)
    {
        $user = $request->user();

        if ($user->isWarga()) {
            abort_unless($user->resident_id && $letterArchive->resident_id === $user->resident_id, 403, 'Anda tidak memiliki akses ke arsip ini.');
        }

        abort_unless($letterArchive->generated_pdf_path, 404, 'File PDF tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($letterArchive->generated_pdf_path), 404, 'File tidak ditemukan.');

        return response()->file(
            Storage::disk('public')->path($letterArchive->generated_pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadDocx(Request $request, LetterArchive $letterArchive)
    {
        $user = $request->user();

        if ($user->isWarga()) {
            abort_unless($user->resident_id && $letterArchive->resident_id === $user->resident_id, 403, 'Anda tidak memiliki akses ke arsip ini.');
        }

        abort_unless($letterArchive->generated_docx_path, 404, 'File DOCX tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($letterArchive->generated_docx_path), 404, 'File tidak ditemukan.');

        return response()->download(Storage::disk('public')->path($letterArchive->generated_docx_path), basename($letterArchive->generated_docx_path));
    }
}