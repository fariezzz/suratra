<?php

namespace App\Http\Controllers;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterRequest;
use App\Models\Resident;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('resident');
        $query = LetterRequest::query();
        $residentQuery = Resident::query();

        if ($user->isWarga()) {
            abort_unless($user->resident_id, 403, 'Akun warga belum terhubung ke data penduduk.');
            $query->where('resident_id', $user->resident_id);
            $residentQuery->whereKey($user->resident_id);
        } elseif ($user->isRt()) {
            abort_unless($user->managed_rt, 403, 'Akun RT belum terhubung ke wilayah RT.');

            $query->whereHas('resident', function (Builder $residentQuery) use ($user): void {
                $residentQuery->where('rt', $user->managed_rt);
            });

            $residentQuery->where('rt', $user->managed_rt);
        }

        $statusCounts = collect(LetterRequestStatus::cases())->map(fn (LetterRequestStatus $status) => [
            'label' => $status->label(),
            'value' => (clone $query)->where('status', $status->value)->count(),
            'badge' => $status->badgeClass(),
        ]);

        $rtSummaries = collect();
        $selectedRt = null;
        $rtChartMetrics = collect();
        $residentProfile = null;
        $activeResidentRequests = collect();
        $latestResidentRequest = null;
        $residentStats = null;
        $residentYearlyMetrics = collect();

        if ($user->isRw() || $user->isRt()) {
            $rtSummaries = Resident::query()
                ->select('rt')
                ->selectRaw('COUNT(*) as total_warga')
                ->selectRaw("SUM(CASE WHEN resident_status = 'warga_asli' THEN 1 ELSE 0 END) as warga_asli_count")
                ->selectRaw("SUM(CASE WHEN resident_status = 'pendatang' THEN 1 ELSE 0 END) as pendatang_count")
                ->when($user->isRt(), fn ($builder) => $builder->where('rt', $user->managed_rt))
                ->groupBy('rt')
                ->orderBy('rt')
                ->get()
                ->map(function ($item) {
                    $item->status_label = ((int) $item->pendatang_count) > 0 ? 'Campuran' : 'Dominan Warga Asli';

                    return $item;
                });

            $availableRts = $rtSummaries->pluck('rt')->values();
            $requestedRt = $request->string('rt')->toString();

            $selectedRt = $user->isRt()
                ? $user->managed_rt
                : ($availableRts->contains($requestedRt) ? $requestedRt : $availableRts->first());

            if ($selectedRt) {
                $selectedResidents = Resident::query()->where('rt', $selectedRt);

                $rtChartMetrics = collect([
                    [
                        'label' => 'Total Warga',
                        'value' => (clone $selectedResidents)->count(),
                        'color' => '#fbbf24',
                    ],
                    [
                        'label' => 'Warga Asli',
                        'value' => (clone $selectedResidents)->where('resident_status', 'warga_asli')->count(),
                        'color' => '#1f8b4c',
                    ],
                    [
                        'label' => 'Pendatang',
                        'value' => (clone $selectedResidents)->where('resident_status', 'pendatang')->count(),
                        'color' => '#2563eb',
                    ],
                ]);

                $residentYearlyRaw = Resident::query()
                    ->selectRaw('YEAR(created_at) as year')
                    ->selectRaw("SUM(CASE WHEN resident_status = 'warga_asli' THEN 1 ELSE 0 END) as warga_asli_count")
                    ->selectRaw("SUM(CASE WHEN resident_status = 'pendatang' THEN 1 ELSE 0 END) as pendatang_count")
                    ->where('rt', $selectedRt)
                    ->whereNotNull('created_at')
                    ->groupBy(DB::raw('YEAR(created_at)'))
                    ->orderBy('year')
                    ->get();

                $residentYearlyMetrics = $residentYearlyRaw
                    ->map(fn ($item) => [
                        'year' => (string) $item->year,
                        'warga_asli' => (int) $item->warga_asli_count,
                        'pendatang' => (int) $item->pendatang_count,
                    ]);
            }
        }

        if ($user->isWarga()) {
            $residentProfile = $user->resident;
            $activeResidentRequests = LetterRequest::query()
                ->where('resident_id', $user->resident_id)
                ->whereIn('status', [
                    LetterRequestStatus::PENDING_RT->value,
                    LetterRequestStatus::PENDING_RW->value,
                    LetterRequestStatus::REJECTED_RT->value,
                    LetterRequestStatus::REJECTED_RW->value,
                ])
                ->latest()
                ->take(4)
                ->get();

            $latestResidentRequest = LetterRequest::query()
                ->where('resident_id', $user->resident_id)
                ->latest()
                ->first();

            $residentStats = [
                'total_requests' => LetterRequest::query()
                    ->where('resident_id', $user->resident_id)
                    ->count(),
                'active_requests' => LetterRequest::query()
                    ->where('resident_id', $user->resident_id)
                    ->whereIn('status', [
                        LetterRequestStatus::PENDING_RT->value,
                        LetterRequestStatus::PENDING_RW->value,
                    ])
                    ->count(),
                'completed_requests' => LetterRequest::query()
                    ->where('resident_id', $user->resident_id)
                    ->where('status', LetterRequestStatus::COMPLETED->value)
                    ->count(),
            ];
        }

        return view('dashboard', [
            'title' => 'Dashboard',
            'isWarga' => $user->isWarga(),
            'isRt' => $user->isRt(),
            'isRw' => $user->isRw(),
            'roleLabel' => $user->role->label(),
            'managedRt' => $user->managed_rt,
            'residentCount' => (clone $residentQuery)->count(),
            'residentProfile' => $residentProfile,
            'residentStats' => $residentStats,
            'activeResidentRequests' => $activeResidentRequests,
            'latestResidentRequest' => $latestResidentRequest,
            'letterTypes' => LetterType::options(),
            'rtCount' => $user->isRw() ? $rtSummaries->count() : null,
            'rtSummaries' => $rtSummaries,
            'selectedRt' => $selectedRt,
            'rtChartMetrics' => $rtChartMetrics,
            'residentYearlyMetrics' => $residentYearlyMetrics,
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
