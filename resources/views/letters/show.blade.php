@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i>Cetak Surat
        </button>
    </div>

    <div class="letter-sheet p-4 p-lg-5 letter-body">
        {!! $letterRequest->generated_content !!}
    </div>
@endsection
