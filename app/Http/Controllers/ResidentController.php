<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));

        $residents = Resident::query()
            ->with('user')
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($inner) use ($keyword): void {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%")
                        ->orWhere('address', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('residents.index', compact('residents', 'keyword'));
    }

    public function create(): View
    {
        return view('residents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedResident = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:residents,nik'],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'rt' => ['required', 'digits:3'],
            'rw' => ['required', 'digits:3'],
            'phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validatedResident): void {
            $resident = Resident::query()->create([
                'nik' => $validatedResident['nik'],
                'name' => $validatedResident['name'],
                'gender' => $validatedResident['gender'],
                'birth_place' => $validatedResident['birth_place'] ?? null,
                'birth_date' => $validatedResident['birth_date'] ?? null,
                'address' => $validatedResident['address'],
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
        $resident->load('user');

        return view('residents.edit', compact('resident'));
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $resident->load('user');

        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('residents', 'nik')->ignore($resident->id)],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['required', 'string', 'max:255'],
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
                'address' => $validated['address'],
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
        DB::transaction(function () use ($resident): void {
            User::query()->where('resident_id', $resident->id)->delete();
            $resident->delete();
        });

        return to_route('residents.index')->with('success', 'Data warga berhasil dihapus.');
    }
}
