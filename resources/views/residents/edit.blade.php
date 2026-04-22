@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Ubah Data Warga</h1>
            <p class="text-muted mb-0">{{ $resident->name }} (NIK: {{ $resident->nik }})</p>
        </div>
        <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form action="{{ route('residents.update', $resident) }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                @method('PUT')
                @include('residents._form', ['resident' => $resident])
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
