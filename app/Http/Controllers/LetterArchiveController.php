<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterArchive;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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
}
