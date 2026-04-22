@extends('layouts.guest')

@section('content')
    <div class="card border-0 shadow-sm login-card mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="login-icon mb-3">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h1 class="h4 mb-1">Sistem Informasi Persuratan RT/RW</h1>
                <p class="text-muted mb-0">Silakan login sesuai akun yang diberikan pengurus.</p>
            </div>

            <form action="{{ route('login.store') }}" method="POST" class="d-flex flex-column gap-3">
                @csrf

                <div>
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </button>
            </form>
        </div>
    </div>
@endsection
