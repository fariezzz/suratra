<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Models\LetterRequest;
use App\Models\Resident;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('resident');
        $query = LetterRequest::query();

        if ($user->isWarga()) {
            abort_unless($user->resident_id, 403, 'Akun warga belum terhubung ke data penduduk.');
            $query->where('resident_id', $user->resident_id);
        }

        $statusCounts = collect(LetterRequestStatus::cases())->map(fn (LetterRequestStatus $status) => [
            'label' => $status->label(),
            'value' => (clone $query)->where('status', $status->value)->count(),
            'badge' => $status->badgeClass(),
        ]);

        return view('dashboard', [
            'isWarga' => $user->isWarga(),
            'roleLabel' => $user->role->label(),
            'residentCount' => $user->isWarga() ? 1 : Resident::query()->count(),
            'requestCount' => (clone $query)->count(),
            'completedCount' => (clone $query)->where('status', LetterRequestStatus::COMPLETED->value)->count(),
            'statusCounts' => $statusCounts,
            'latestRequests' => (clone $query)
                ->with('resident')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}
