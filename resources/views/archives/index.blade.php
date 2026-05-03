@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Arsip Surat</h1>
            <p class="text-muted mb-0">
                @if ($isWarga)
                    Riwayat surat Anda yang sudah selesai dan tersimpan di arsip.
                @else
                    Telusuri seluruh surat yang sudah selesai dibuat dan masuk ke arsip.
                @endif
            </p>
        </div>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('archives.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label" for="q">Cari Arsip</label>
                    <input
                        type="text"
                        class="form-control"
                        id="q"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Nama warga, NIK, nomor referensi, atau nomor surat"
                    >
                </div>
                <div class="col-lg-4">
                    <label class="form-label" for="letter_type">Jenis Surat</label>
                    <select class="form-select" id="letter_type" name="letter_type">
                        <option value="">Semua jenis surat</option>
                        @foreach ($letterTypes as $value => $label)
                            <option value="{{ $value }}" @selected($selectedLetterType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Daftar Arsip</strong>
            <span class="small text-muted">{{ $archives->total() }} surat</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nomor Arsip</th>
                        <th>Nomor Surat</th>
                        <th>Pemohon</th>
                        <th>Jenis Surat</th>
                        <th>Tanggal Arsip</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archives as $archive)
                        <tr>
                            <td>{{ $archive->archive_number ?: '-' }}</td>
                            <td>
                                <div>{{ $archive->letter_number ?: '-' }}</div>
                                <small class="text-muted">{{ $archive->reference_number }}</small>
                            </td>
                            <td>
                                <div>{{ $archive->resident_name }}</div>
                                <small class="text-muted">NIK {{ $archive->resident_nik }}</small>
                            </td>
                            <td>{{ $archive->letter_type_label }}</td>
                            <td>{{ $archive->archived_at?->translatedFormat('d F Y H:i') ?: '-' }}</td>
                            <td>
                                <a href="{{ route('archives.show', $archive) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-1"></i>Lihat Arsip
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada arsip surat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $archives->links() }}
        </div>
    </div>
@endsection
