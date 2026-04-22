@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Tambah Data Warga</h1>
            <p class="text-muted mb-0">Sekaligus membuat akun login warga.</p>
        </div>
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form action="{{ route('residents.store') }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                @include('residents._form')
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
