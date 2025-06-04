<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'DelBites Admin')</title>

    {{-- CSRF Token untuk AJAX requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    {{-- Menggunakan versi 6.0.0 dari Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS untuk layout admin -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            /* Asumsi font Nunito tersedia */
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
            padding-top: 1rem;
            /* Tambahkan padding atas */
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.7rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.25rem;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: white;
            background-color: #0d6efd;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }

        .content-wrapper {
            min-height: 100vh;
            background-color: #f8f9fa;
            padding-top: 1rem;
            /* Tambahkan padding atas */
        }

        /* Responsive adjustments for sidebar */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
                padding: 0;
                width: 250px;
                /* Lebar sidebar saat mobile */
                overflow-y: auto;
                transform: translateX(-100%);
                /* Sembunyikan sidebar secara default */
                transition: transform 0.3s ease-in-out;
            }

            .sidebar.show {
                transform: translateX(0%);
                /* Tampilkan sidebar saat aktif */
            }

            .content-wrapper {
                margin-left: 0;
                /* Pastikan tidak ada margin kiri di mobile */
            }

            .navbar-toggler {
                display: block;
                /* Tampilkan toggler di mobile */
            }
        }
    </style>

    {{-- Custom styles from child views --}}
    @yield('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="d-flex align-items-center justify-content-center mb-4 p-3">
                        <i class="fas fa-utensils me-2 fs-4 text-white"></i> {{-- Mengubah ikon dan menambahkan warna --}}
                        <span class="fs-4 text-white">Del<strong>Bites</strong></span>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('pesanan*') ? 'active' : '' }}"
                                href="{{ route('pesanan.index') }}">
                                <i class="fas fa-shopping-cart"></i> Pesanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('produk*') ? 'active' : '' }}"
                                href="{{ route('produk.index') }}">
                                <i class="fas fa-box"></i> Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('pelanggan*') ? 'active' : '' }}"
                                href="{{ route('pelanggan.index') }}">
                                <i class="fas fa-users"></i> Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}"
                                href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('stok*') ? 'active' : '' }}"
                                href="{{ route('stok.index') }}">
                                <i class="fas fa-boxes"></i> Stok
                            </a>
                        </li>
                    </ul>

                    <hr class="text-white-50">

                    <div class="text-center p-3 text-white-50">
                        &copy; DelBites 2025
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 content-wrapper">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-4">
                    <div class="container-fluid">
                        {{-- Toggle button for mobile sidebar --}}
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse"
                            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>

                        <div class="d-flex align-items-center ms-auto"> {{-- ms-auto untuk push ke kanan --}}
                            <!-- User Dropdown -->
                            <div class="dropdown p-2">
                                <a href="#" class="d-flex align-items-center text-decoration-none"
                                    id="dropdownUserDetails" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ auth()->user()->foto ? asset('storage/' . auth()->user()->foto) : asset('default.png') }}"
                                        alt="User Image" class="rounded-circle me-2" width="40" height="40">
                                    <span class="d-none d-lg-inline text-dark">{{ auth()->user()->name }}</span>
                                    {{-- Tampilkan nama user --}}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end text-center shadow p-3"
                                    aria-labelledby="dropdownUserDetails" style="width: 250px;">
                                    <li>
                                        <img src="{{ auth()->user()->foto ? asset('storage/' . auth()->user()->foto) : asset('storage/logo1.png') }}"
                                            alt="User Profile Image" class="rounded-circle mb-2" width="80"
                                            height="80">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header">{{ auth()->user()->name }}</h6>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('profil.index') }}">Profile</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">Sign out</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Page Content -->
                <main class="pb-5">
                    @yield('content')
                </main>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (jika masih diperlukan oleh skrip lain, jika tidak bisa dihapus) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Custom scripts from child views --}}
    @yield('scripts')
</body>

</html>
