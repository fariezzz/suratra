@extends('layouts.app')

@section('content')
    @php
        $completionRate = $requestCount > 0 ? (int) round(($completedCount / $requestCount) * 100) : 0;
    @endphp

    <section class="dashboard-hero card card-soft mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="dashboard-eyebrow mb-1">Sistem Informasi Persuratan RT/RW</p>
                <h1 class="h3 mb-2">Dashboard Persuratan</h1>
                <p class="text-muted mb-0">Akses Anda sebagai <strong>{{ $roleLabel }}</strong>.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if ($isRw && $rtCount)
                    <span class="dashboard-chip"><i class="bi bi-diagram-3 me-1"></i>{{ $rtCount }} RT aktif</span>
                @endif
                @if (! $isWarga)
                    <span class="dashboard-chip"><i class="bi bi-patch-check me-1"></i>{{ $completionRate }}% selesai</span>
                @endif
                <a href="{{ route('letters.index') }}" class="btn btn-success">
                    <i class="bi bi-envelope-plus me-1"></i>Lihat Persuratan
                </a>
            </div>
        </div>
    </section>

    @if (! $isWarga)
        <div class="row g-3 mb-4 dashboard-metric-row">
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Total Warga</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $residentCount }}</h2>
                        </div>
                        <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Total Pengajuan</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $requestCount }}</h2>
                        </div>
                        <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-envelope-paper"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Pengajuan Selesai</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $completedCount }}</h2>
                            <p class="small text-muted mb-0 mt-1">{{ $completionRate }}% dari total pengajuan</p>
                        </div>
                        <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-patch-check"></i></span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($isWarga && $residentProfile)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Total Pengajuan Saya</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $residentStats['total_requests'] }}</h2>
                        </div>
                        <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-envelope-paper"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Sedang Diproses</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $residentStats['active_requests'] }}</h2>
                        </div>
                        <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft h-100 dashboard-metric-card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted text-uppercase small mb-1">Surat Selesai</p>
                            <h2 class="h3 mb-0 fw-bold">{{ $residentStats['completed_requests'] }}</h2>
                        </div>
                        <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-patch-check"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Ringkasan Akun</strong>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <div class="text-muted small">Nama</div>
                            <div class="fw-semibold">{{ $residentProfile->name }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">NIK</div>
                            <div>{{ $residentProfile->nik }}</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-muted small">RT/RW</div>
                                <div>{{ $residentProfile->rt }}/{{ $residentProfile->rw }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Status</div>
                                <div>{{ $residentProfile->resident_status === 'warga_asli' ? 'Warga Asli' : 'Pendatang' }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-muted small">Alamat Saat Ini</div>
                            <div>{{ $residentProfile->address }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 pt-2">
                            <a href="{{ route('letters.index') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-envelope-plus me-1"></i>Ajukan Surat
                            </a>
                            <a href="{{ route('archives.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-archive me-1"></i>Arsip Surat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Layanan Tersedia</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            @foreach ($letterTypes as $label)
                                <div class="dashboard-service-item small">{{ $label }}</div>
                            @endforeach
                        </div>
                        <div class="small text-muted mt-3">
                            Dokumen persyaratan akan menyesuaikan otomatis saat Anda memilih jenis surat.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Pengajuan Terakhir</strong>
                    </div>
                    <div class="card-body">
                        @if ($latestResidentRequest)
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <div class="text-muted small">Jenis Surat</div>
                                    <div class="fw-semibold">{{ $latestResidentRequest->letter_type_label }}</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Status</div>
                                    <span class="badge status-badge {{ $latestResidentRequest->status_badge_class }}">{{ $latestResidentRequest->status_label }}</span>
                                </div>
                                <div>
                                    <div class="text-muted small">Nomor Referensi</div>
                                    <div>{{ $latestResidentRequest->reference_number }}</div>
                                </div>
                                <div class="pt-1">
                                    <a href="{{ route('letters.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list-check me-1"></i>Lihat Detail Pengajuan
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-muted mb-3">Belum ada riwayat pengajuan surat.</div>
                            <a href="{{ route('letters.index') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-envelope-plus me-1"></i>Mulai Ajukan Surat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($activeResidentRequests->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card card-soft h-100">
                        <div class="card-header bg-white dashboard-section-header">
                            <strong>Pengajuan yang Perlu Diperhatikan</strong>
                        </div>
                        <div class="card-body">
                            @foreach ($activeResidentRequests as $requestItem)
                                <div class="dashboard-active-item {{ ! $loop->last ? 'mb-2' : '' }}">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $requestItem->letter_type_label }}</div>
                                            <div class="small text-muted">{{ $requestItem->reference_number }}</div>
                                        </div>
                                        <span class="badge status-badge {{ $requestItem->status_badge_class }}">{{ $requestItem->status_label }}</span>
                                    </div>
                                    @if ($requestItem->rt_notes || $requestItem->rw_notes)
                                        <div class="small mt-2">
                                            {{ $requestItem->rw_notes ?? $requestItem->rt_notes }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if (! $isWarga)
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Status Pengajuan</strong>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        @php
                            $totalStatuses = max(1, $requestCount);
                        @endphp
                        @foreach ($statusCounts as $status)
                            @php
                                $statusPercentage = (int) round(($status['value'] / $totalStatuses) * 100);
                            @endphp
                            <div class="dashboard-status-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>{{ $status['label'] }}</span>
                                    <span class="badge status-badge {{ $status['badge'] }}">{{ $status['value'] }}</span>
                                </div>
                                <div class="progress dashboard-progress">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $statusPercentage }}%;"
                                        aria-valuenow="{{ $statusPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $statusPercentage }}%
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Pengajuan Terbaru</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
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
    @endif

    @if ($isRt || $isRw)
        <div class="card card-soft mt-4">
            <div class="card-header bg-white dashboard-section-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $isRw ? 'Ringkasan RT' : 'Ringkasan RT Saya' }}</strong>
                    <div class="small text-muted">Distribusi warga tiap RT untuk membantu pemantauan wilayah.</div>
                </div>
                <a href="{{ route('residents.rt-overview') }}" class="btn btn-sm btn-outline-success flex-shrink-0">
                    {{ $isRw ? 'Lihat Semua RT' : 'Lihat Warga RT '.$managedRt }}
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>RT</th>
                            <th>Total Warga</th>
                            <th>Warga Asli</th>
                            <th>Pendatang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rtSummaries as $summary)
                            <tr>
                                <td>
                                    <a href="{{ route('residents.rt-residents', $summary->rt) }}" class="text-decoration-none">
                                        RT {{ $summary->rt }}
                                    </a>
                                </td>
                                <td>{{ $summary->total_warga }}</td>
                                <td>{{ $summary->warga_asli_count }}</td>
                                <td>{{ $summary->pendatang_count }}</td>
                                <td>{{ $summary->status_label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data RT.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-xl-5">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <strong>Komposisi Warga RT {{ $selectedRt }}</strong>
                            <div class="small text-muted">Perbandingan total warga, warga asli, dan pendatang saat ini.</div>
                        </div>
                        @if ($isRw)
                            <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
                                <label for="rt" class="small text-muted">Pilih RT</label>
                                <select name="rt" id="rt" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach ($rtSummaries as $summary)
                                        <option value="{{ $summary->rt }}" @selected($selectedRt === $summary->rt)>RT {{ $summary->rt }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="badge text-bg-light border">RT {{ $selectedRt }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($rtChartMetrics->isNotEmpty())
                            <div class="rt-single-chart-card">
                                <div class="rt-single-chart-canvas rt-single-chart-canvas-sm">
                                    <canvas id="rtResidentCompositionChart" aria-label="Diagram batang komposisi warga RT {{ $selectedRt }}"></canvas>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">Belum ada data warga RT untuk ditampilkan.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card card-soft h-100">
                    <div class="card-header bg-white dashboard-section-header">
                        <strong>Tren Pendaftaran Warga per Tahun</strong>
                        <div class="small text-muted">Menggunakan tahun pembuatan data warga pada RT {{ $selectedRt }}.</div>
                    </div>
                    <div class="card-body">
                        @if ($residentYearlyMetrics->isNotEmpty())
                            <div class="rt-single-chart-card">
                                <div class="rt-single-chart-canvas">
                                    <canvas id="rtResidentYearlyChart" aria-label="Diagram tren pendaftaran warga per tahun RT {{ $selectedRt }}"></canvas>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">Belum ada data tahunan warga untuk ditampilkan.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@if (($isRt || $isRw) && ($rtChartMetrics->isNotEmpty() || $residentYearlyMetrics->isNotEmpty()))
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            (() => {
                if (typeof Chart === 'undefined') {
                    return;
                }

                const baseOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                };

                const compositionCanvas = document.getElementById('rtResidentCompositionChart');

                if (compositionCanvas) {
                    const metrics = @json($rtChartMetrics->values());
                    const labels = metrics.map((item) => item.label);
                    const values = metrics.map((item) => item.value);
                    const colors = metrics.map((item) => item.color);
                    const suggestedMax = Math.max(...values, 1);

                    new Chart(compositionCanvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                data: values,
                                backgroundColor: colors,
                                borderRadius: 0,
                                borderSkipped: false,
                                barThickness: 54,
                                maxBarThickness: 62,
                            }],
                        },
                        options: {
                            ...baseOptions,
                            plugins: {
                                ...baseOptions.plugins,
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `${context.label}: ${context.raw}`,
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: '#374151',
                                        font: {
                                            size: 12,
                                            weight: '600',
                                        },
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    suggestedMax,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0,
                                        color: '#6b7280',
                                        font: {
                                            size: 11,
                                        },
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.22)',
                                        drawBorder: false,
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                            },
                        },
                    });
                }

                const yearlyCanvas = document.getElementById('rtResidentYearlyChart');

                if (yearlyCanvas) {
                    const yearlyMetrics = @json($residentYearlyMetrics->values());
                    const yearlyLabels = yearlyMetrics.map((item) => item.year);

                    new Chart(yearlyCanvas, {
                        type: 'bar',
                        data: {
                            labels: yearlyLabels,
                            datasets: [
                                {
                                    label: 'Warga Asli',
                                    data: yearlyMetrics.map((item) => item.warga_asli),
                                    backgroundColor: '#1f8b4c',
                                    borderRadius: 0,
                                    borderSkipped: false,
                                    categoryPercentage: 0.8,
                                    barPercentage: 0.9,
                                    maxBarThickness: 56,
                                },
                                {
                                    label: 'Pendatang',
                                    data: yearlyMetrics.map((item) => item.pendatang),
                                    backgroundColor: '#2563eb',
                                    borderRadius: 0,
                                    borderSkipped: false,
                                    categoryPercentage: 0.8,
                                    barPercentage: 0.9,
                                    maxBarThickness: 56,
                                },
                            ],
                        },
                        options: {
                            ...baseOptions,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        color: '#374151',
                                        boxWidth: 10,
                                        boxHeight: 10,
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    stacked: false,
                                    grid: {
                                        display: false,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: '#374151',
                                        font: {
                                            size: 12,
                                            weight: '600',
                                        },
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0,
                                        color: '#6b7280',
                                        font: {
                                            size: 11,
                                        },
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.22)',
                                        drawBorder: false,
                                    },
                                    border: {
                                        display: false,
                                    },
                                },
                            },
                        },
                    });
                }
            })();
        </script>
    @endpush
@endif
