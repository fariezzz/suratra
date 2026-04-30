<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Suratra | Sistem Informasi Persuratan RT/RW' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="bi bi-building-check"></i>
                <span>Suratra</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu"
                aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('dashboard')) active @endif"
                            href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    @if (auth()->user()->isRt() || auth()->user()->isRw())
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('residents.*')) active @endif"
                                href="{{ route('residents.index') }}">
                                <i class="bi bi-people me-1"></i>Data Warga
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('letters.*')) active @endif"
                            href="{{ route('letters.index') }}">
                            <i class="bi bi-envelope-paper me-1"></i>Persuratan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('archives.*')) active @endif"
                            href="{{ route('archives.index') }}">
                            <i class="bi bi-archive me-1"></i>Arsip Surat
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <button class="btn btn-success nav-link dropdown-toggle border-0" data-bs-toggle="dropdown"
                            type="button">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span
                                    class="dropdown-item-text text-muted">{{ auth()->user()->role?->label() ?? 'Pengguna' }}</span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="px-3">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum valid.</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    @stack('scripts')
</body>

</html>
