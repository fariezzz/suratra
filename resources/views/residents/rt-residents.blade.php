@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Daftar Warga RT {{ $rt }}</h1>
            <p class="text-muted mb-0">Menampilkan warga pada RT ini beserta status warga dan alamatnya.</p>
        </div>
        <a href="{{ route('residents.rt-overview') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card card-soft">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Status Warga</th>
                        <th>Alamat KTP</th>
                        <th>Alamat Saat Ini</th>
                        <th>RW</th>
                        <th>Email Akun</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($residents as $resident)
                        <tr>
                            <td>{{ $resident->nik }}</td>
                            <td>{{ $resident->name }}</td>
                            <td>{{ $resident->resident_status === 'warga_asli' ? 'Warga Asli' : 'Pendatang' }}</td>
                            <td>{{ $resident->ktp_address }}</td>
                            <td>{{ $resident->address }}</td>
                            <td>{{ $resident->rw }}</td>
                            <td>{{ $resident->user?->email ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada warga pada RT ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $residents->links() }}
        </div>
    </div>
@endsection
