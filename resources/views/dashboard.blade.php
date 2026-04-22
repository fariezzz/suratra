@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 mb-1">Dashboard Persuratan</h1>
            <p class="text-muted mb-0">Akses Anda sebagai <strong>{{ $roleLabel }}</strong>.</p>
        </div>
        <a href="{{ route('letters.index') }}" class="btn btn-success">
            <i class="bi bi-envelope-plus me-1"></i>Lihat Persuratan
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">{{ $isWarga ? 'Data Warga' : 'Total Warga' }}</p>
                        <h2 class="h3 mb-0">{{ $residentCount }}</h2>
                    </div>
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Pengajuan</p>
                        <h2 class="h3 mb-0">{{ $requestCount }}</h2>
                    </div>
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-envelope-paper"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-soft h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Pengajuan Selesai</p>
                        <h2 class="h3 mb-0">{{ $completedCount }}</h2>
                    </div>
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-patch-check"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card card-soft h-100">
                <div class="card-header bg-white">
                    <strong>Status Pengajuan</strong>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @foreach ($statusCounts as $status)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2">
                            <span>{{ $status['label'] }}</span>
                            <span class="badge status-badge {{ $status['badge'] }}">{{ $status['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-soft h-100">
                <div class="card-header bg-white">
                    <strong>Pengajuan Terbaru</strong>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Referensi</th>
                                <th>Warga</th>
                                <th>Jenis Surat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestRequests as $requestItem)
                                <tr>
                                    <td>{{ $requestItem->reference_number }}</td>
                                    <td>{{ $requestItem->resident->name }}</td>
                                    <td>{{ $requestItem->letter_type_label }}</td>
                                    <td><span class="badge status-badge {{ $requestItem->status_badge_class }}">{{ $requestItem->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data pengajuan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
