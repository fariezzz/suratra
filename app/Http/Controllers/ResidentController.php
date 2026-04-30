<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $keyword = trim((string) $request->query('q', ''));
        $gender = trim((string) $request->query('gender', ''));
        $residentStatus = trim((string) $request->query('resident_status', ''));

        if (! in_array($gender, ['L', 'P'], true)) {
            $gender = '';
        }

        if (! in_array($residentStatus, ['warga_asli', 'pendatang'], true)) {
            $residentStatus = '';
        }

        $residents = $this->visibleResidentsQuery($user)
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($inner) use ($keyword): void {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%")
                        ->orWhere('ktp_address', 'like', "%{$keyword}%")
                        ->orWhere('address', 'like', "%{$keyword}%");
                });
            })
            ->when($gender !== '', fn ($query) => $query->where('gender', $gender))
            ->when($residentStatus !== '', fn ($query) => $query->where('resident_status', $residentStatus))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('residents.index', compact('residents', 'keyword', 'gender', 'residentStatus'));
    }

    public function rtOverview(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isRt() || $user->isRw(), 403);
        abort_unless(! $user->isRt() || $user->managed_rt, 403, 'Akun RT belum terhubung ke wilayah RT.');

        $rtSummaries = Resident::query()
            ->select('rt')
            ->selectRaw('COUNT(*) as total_warga')
            ->selectRaw("SUM(CASE WHEN resident_status = 'warga_asli' THEN 1 ELSE 0 END) as warga_asli_count")
            ->selectRaw("SUM(CASE WHEN resident_status = 'pendatang' THEN 1 ELSE 0 END) as pendatang_count")
            ->when($user->isRt(), fn ($query) => $query->where('rt', $user->managed_rt))
            ->groupBy('rt')
            ->orderBy('rt')
            ->get()
            ->map(function ($item) {
                $item->status_label = ((int) $item->pendatang_count) > 0 ? 'Campuran' : 'Dominan Warga Asli';

                return $item;
            });

        return view('residents.rt-overview', [
            'rtSummaries' => $rtSummaries,
            'isRt' => $user->isRt(),
            'managedRt' => $user->managed_rt,
        ]);
    }

    public function rtResidents(Request $request, string $rt): View
    {
        $user = $request->user();
        abort_unless($user->isRt() || $user->isRw(), 403);
        abort_unless($user->canAccessRt($rt), 403, 'Anda tidak memiliki akses ke data RT ini.');

        $residents = Resident::query()
            ->with('user')
            ->where('rt', $rt)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('residents.rt-residents', [
            'rt' => $rt,
            'residents' => $residents,
            'isRt' => $user->isRt(),
        ]);
    }

    public function create(): View
    {
        return view('residents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validatedResident = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:residents,nik'],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'ktp_address' => ['required', 'string', 'max:255'],
            'status_kawin' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'resident_status' => ['required', Rule::in(['warga_asli', 'pendatang'])],
            'rt' => ['required', 'digits:3'],
            'rw' => ['required', 'digits:3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->isRt()) {
            abort_unless($user->managed_rt, 403, 'Akun RT belum terhubung ke wilayah RT.');
            $validatedResident['rt'] = $user->managed_rt;
        }

        DB::transaction(function () use ($validatedResident): void {
            $resident = Resident::query()->create([
                'nik' => $validatedResident['nik'],
                'name' => $validatedResident['name'],
                'gender' => $validatedResident['gender'],
                'birth_place' => $validatedResident['birth_place'] ?? null,
                'birth_date' => $validatedResident['birth_date'] ?? null,
                'ktp_address' => $validatedResident['ktp_address'],
                'status_kawin' => $validatedResident['status_kawin'] ?? null,
                'agama' => $validatedResident['agama'] ?? null,
                'address' => $validatedResident['address'],
                'resident_status' => $validatedResident['resident_status'],
                'rt' => $validatedResident['rt'],
                'rw' => $validatedResident['rw'],
                'phone' => $validatedResident['phone'] ?? null,
                'occupation' => $validatedResident['occupation'] ?? null,
            ]);

            User::query()->create([
                'name' => $resident->name,
                'email' => $validatedResident['email'],
                'password' => Hash::make($validatedResident['password']),
                'role' => UserRole::WARGA,
                'resident_id' => $resident->id,
            ]);
        });

        return to_route('residents.index')->with('success', 'Data warga dan akun login berhasil dibuat.');
    }

    public function edit(Resident $resident): View
    {
        $this->authorizeResidentAccess(request()->user(), $resident);
        $resident->load('user');

        return view('residents.edit', compact('resident'));
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $this->authorizeResidentAccess($request->user(), $resident);
        $resident->load('user');

        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('residents', 'nik')->ignore($resident->id)],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'ktp_address' => ['required', 'string', 'max:255'],
            'status_kawin' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'resident_status' => ['required', Rule::in(['warga_asli', 'pendatang'])],
            'rt' => ['required', 'digits:3'],
            'rw' => ['required', 'digits:3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($resident->user?->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->user()->isRt()) {
            $validated['rt'] = $request->user()->managed_rt;
        }

        if (! $resident->user && empty($validated['password'])) {
            return back()
                ->withErrors(['password' => 'Password wajib diisi untuk membuat akun warga.'])
                ->withInput();
        }

        DB::transaction(function () use ($resident, $validated): void {
            $resident->update([
                'nik' => $validated['nik'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'ktp_address' => $validated['ktp_address'],
                'status_kawin' => $validated['status_kawin'] ?? null,
                'agama' => $validated['agama'] ?? null,
                'address' => $validated['address'],
                'resident_status' => $validated['resident_status'],
                'rt' => $validated['rt'],
                'rw' => $validated['rw'],
                'phone' => $validated['phone'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
            ]);

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => UserRole::WARGA,
                'resident_id' => $resident->id,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if ($resident->user) {
                $resident->user->update($userData);
            } else {
                User::query()->create([
                    ...$userData,
                    'password' => $userData['password'],
                ]);
            }
        });

        return to_route('residents.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $this->authorizeResidentAccess(request()->user(), $resident);

        DB::transaction(function () use ($resident): void {
            User::query()->where('resident_id', $resident->id)->delete();
            $resident->delete();
        });

        return to_route('residents.index')->with('success', 'Data warga berhasil dihapus.');
    }

    private function visibleResidentsQuery(User $user): Builder
    {
        return Resident::query()
            ->when($user->isRt(), function (Builder $query) use ($user): void {
                abort_unless($user->managed_rt, 403, 'Akun RT belum terhubung ke wilayah RT.');
                $query->where('rt', $user->managed_rt);
            });
    }

    private function authorizeResidentAccess(User $user, Resident $resident): void
    {
        if ($user->isRt()) {
            abort_unless($user->managed_rt, 403, 'Akun RT belum terhubung ke wilayah RT.');
            abort_unless($resident->rt === $user->managed_rt, 403, 'Anda tidak memiliki akses ke data warga RT lain.');
        }
    }
}
