@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Detail Warga</h1>
            <p class="text-muted mb-0">Informasi lengkap data kependudukan dan akun login.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('residents.edit', $resident) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i>Edit Data
            </a>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white">
            <strong>{{ $resident->name }}</strong>
            <span class="text-muted ms-2">NIK {{ $resident->nik }}</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Nama</th>
                                    <td>{{ $resident->name }}</td>
                                </tr>
                                <tr>
                                    <th>NIK</th>
                                    <td>{{ $resident->nik }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <td>{{ $resident->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                </tr>
                                <tr>
                                    <th>Tempat, Tanggal Lahir</th>
                                    <td>
                                        {{ $resident->birth_place ?: '-' }}
                                        @if ($resident->birth_date)
                                            , {{ $resident->birth_date->translatedFormat('d F Y') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Warga</th>
                                    <td>{{ $resident->resident_status === 'warga_asli' ? 'Warga Asli' : 'Pendatang' }}</td>
                                </tr>
                                <tr>
                                    <th>Agama</th>
                                    <td>{{ $resident->agama ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status Kawin</th>
                                    <td>{{ $resident->status_kawin ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Alamat KTP</th>
                                    <td>{{ $resident->ktp_address }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat Domisili</th>
                                    <td>{{ $resident->address }}</td>
                                </tr>
                                <tr>
                                    <th>RT/RW</th>
                                    <td>{{ $resident->rt }}/{{ $resident->rw }}</td>
                                </tr>
                                <tr>
                                    <th>No. HP</th>
                                    <td>{{ $resident->phone ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Pekerjaan</th>
                                    <td>{{ $resident->occupation ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email Akun</th>
                                    <td>{{ $resident->user?->email ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status Akun</th>
                                    <td>{{ $resident->user ? 'Terhubung' : 'Belum terhubung' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection