@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">{{ $isRt ? 'Ringkasan RT Saya' : 'Ringkasan Warga per RT' }}</h1>
            <p class="text-muted mb-0">
                {{ $isRt ? 'Anda hanya dapat melihat RT '.$managedRt.'.' : 'Klik RT untuk melihat daftar warga dan statusnya.' }}
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card card-soft">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>RT</th>
                        <th>Total Warga</th>
                        <th>Warga Asli</th>
                        <th>Pendatang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rtSummaries as $summary)
                        <tr>
                            <td>RT {{ $summary->rt }}</td>
                            <td>{{ $summary->total_warga }}</td>
                            <td>{{ $summary->warga_asli_count }}</td>
                            <td>{{ $summary->pendatang_count }}</td>
                            <td>{{ $summary->status_label }}</td>
                            <td>
                                <a href="{{ route('residents.rt-residents', $summary->rt) }}" class="btn btn-sm btn-outline-success">
                                    Lihat Warga
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data RT.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
