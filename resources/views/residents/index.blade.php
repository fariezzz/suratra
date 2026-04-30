@extends('layouts.app')

@section('content')
    <section class="resident-page-header card card-soft mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="resident-page-eyebrow mb-1">Manajemen Administrasi Warga</p>
                <h1 class="h4 mb-1">Data Warga</h1>
                <p class="text-muted mb-0">Kelola data kependudukan secara rapi dan mudah dipantau.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="resident-page-chip">
                    <i class="bi bi-people me-1"></i>{{ $residents->total() }} data
                </span>
                <a href="{{ route('residents.create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i>Tambah Warga
                </a>
            </div>
        </div>
    </section>

    <div class="card card-soft resident-filter-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('residents.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label small text-muted mb-1" for="q">Pencarian</label>
                    <input type="text" class="form-control" id="q" name="q" value="{{ $keyword }}"
                        placeholder="Nama, NIK, alamat KTP, atau alamat saat ini">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-muted mb-1" for="gender">Jenis Kelamin</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">Semua</option>
                        <option value="L" @selected($gender === 'L')>Laki-laki</option>
                        <option value="P" @selected($gender === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label small text-muted mb-1" for="resident_status">Status</label>
                    <select class="form-select" id="resident_status" name="resident_status">
                        <option value="">Semua</option>
                        <option value="warga_asli" @selected($residentStatus === 'warga_asli')>Warga Asli</option>
                        <option value="pendatang" @selected($residentStatus === 'pendatang')>Pendatang</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-success w-100" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft resident-table-card">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-3 px-lg-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong>Daftar Warga</strong>
                <span class="small text-muted">Menampilkan {{ $residents->count() }} dari {{ $residents->total() }} data</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 resident-table">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Status Warga</th>
                        <th>Alamat</th>
                        <th>RT/RW</th>
                        <th>Email Akun</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($residents as $resident)
                        <tr>
                            <td>
                                <div class="resident-nik">{{ $resident->nik }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $resident->name }}</div>
                                <div class="small text-muted">KTP: {{ $resident->ktp_address }}</div>
                            </td>
                            <td>
                                <span class="resident-pill {{ $resident->gender === 'L' ? 'resident-pill-info' : 'resident-pill-pink' }}">
                                    {{ $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
                            </td>
                            <td>
                                <span class="resident-pill {{ $resident->resident_status === 'warga_asli' ? 'resident-pill-success' : 'resident-pill-warning' }}">
                                    {{ $resident->resident_status === 'warga_asli' ? 'Warga Asli' : 'Pendatang' }}
                                </span>
                            </td>
                            <td class="resident-address">{{ $resident->address }}</td>
                            <td>
                                <span class="resident-rt-pill">{{ $resident->rt }}/{{ $resident->rw }}</span>
                            </td>
                            <td class="resident-email">{{ $resident->user?->email ?? '-' }}</td>
                            <td class="text-end">
                                <div class="resident-actions d-inline-flex gap-2">
                                    <a href="{{ route('residents.edit', $resident) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </a>
                                    <form action="{{ route('residents.destroy', $resident) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus data warga ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-2">Belum ada data warga yang sesuai.</div>
                                <a href="{{ route('residents.create') }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-person-plus me-1"></i>Tambah Data Warga
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 pt-2 pb-3 px-3 px-lg-4">
            {{ $residents->links() }}
        </div>
    </div>
@endsection
