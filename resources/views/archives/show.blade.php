@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('archives.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Arsip
        </a>
        <div class="btn-group">
            @if ($letterArchive->generated_pdf_path)
                <a href="{{ route('archives.pdf', $letterArchive) }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-filetype-pdf me-1"></i>Lihat PDF
                </a>
            @endif
            @if ($letterArchive->generated_docx_path)
                <a href="{{ route('archives.docx', $letterArchive) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-word me-1"></i>Unduh DOCX
                </a>
            @endif
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i>Cetak Surat
            </button>
        </div>
    </div>

    <div class="card card-soft mb-3 no-print">
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-md-3">
                    <div class="text-muted">Nomor Arsip</div>
                    <strong>{{ $letterArchive->archive_number ?: '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Nomor Surat</div>
                    <strong>{{ $letterArchive->letter_number ?: '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Referensi</div>
                    <strong>{{ $letterArchive->reference_number }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Tanggal Arsip</div>
                    <strong>{{ $letterArchive->archived_at?->format('d M Y H:i') ?: '-' }}</strong>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Pemohon</div>
                    <strong>{{ $letterArchive->resident_name }}</strong>
                    <div>NIK {{ $letterArchive->resident_nik }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted">Jenis Surat</div>
                    <strong>{{ $letterArchive->letter_type_label }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="letter-sheet p-4 p-lg-5 letter-body">
        {!! $letterArchive->generated_content !!}
    </div>
@endsection
