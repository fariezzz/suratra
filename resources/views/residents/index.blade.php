@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Data Warga</h1>
            <p class="text-muted mb-0">Dikelola oleh Pengurus RT dan Pengurus RW.</p>
        </div>
        <a href="{{ route('residents.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus me-1"></i>Tambah Warga
        </a>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('residents.index') }}" class="row g-2">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="q" value="{{ $keyword }}" placeholder="Cari nama, NIK, alamat KTP, atau alamat saat ini...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-success w-100" type="submit">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                </div>
            </form>
        </div>
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
                        <th>RT/RW</th>
                        <th>Email Akun</th>
                        <th class="text-end">Aksi</th>
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
                            <td>{{ $resident->rt }}/{{ $resident->rw }}</td>
                            <td>{{ $resident->user?->email ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('residents.destroy', $resident) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data warga ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data warga.</td>
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
