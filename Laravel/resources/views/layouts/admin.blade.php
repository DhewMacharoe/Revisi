<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DelBites Admin')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('icon1.png') }}" type="image/png">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
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
        }

        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
                padding: 0;
                width: 100%;
                overflow-y: auto;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <div class="d-flex align-items-center justify-content-center mb-4 p-3">
                        <i class="fas fa-home me-2"></i>
                        <span class="fs-4">Del<strong>Bites</strong></span>
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
                        {{-- Ikon menu ini telah diubah --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('jadwal-operasional*') ? 'active' : '' }}"
                                href="{{ route('admin.jadwal.index') }}">
                                <i class="fas fa-calendar-alt"></i> Jadwal Operasional
                            </a>
                        </li>   
                    </ul>

                    <hr>

                    <div class="text-center p-3 text-small">
                        Copyright &copy; DelBites <span id="copyright-year"></span>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 content-wrapper ms-sm-auto px-md-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse"
                            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>

                        <div class="d-flex align-items-center">
                            <div class="dropdown p-2">
                                <a href="#" class="d-flex align-items-center text-decoration-none"
                                    id="dropdownUserDetails" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ asset('icon1.png') }}" alt="User Image" class="rounded-circle me-2"
                                        width="40" height="40">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end text-center shadow p-3"
                                    aria-labelledby="dropdownUserDetails" style="width: 250px;">
                                    <img src="{{ asset('icon1.png') }}" alt="User Image"
                                        class="rounded-circle mx-auto d-block mb-2" width="60" height="60">
                                    <strong>{{ auth()->user()->name }}</strong>

                                    <div class="d-flex justify-content-between mt-3">
                                        <a href="{{ route('profil.index') }}"
                                            class="btn btn-outline-primary btn-sm me-1 flex-fill">Profile</a>
                                        <form method="POST" action="{{ route('logout') }}" class="ms-1 flex-fill">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Sign
                                                out</button>
                                        </form>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                <main class="pb-5">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @include('sweetalert::alert')
    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan elemen dengan id 'copyright-year' ada
            const yearSpan = document.getElementById('copyright-year');
            if (yearSpan) {
                yearSpan.textContent = new Date().getFullYear();
            }
        });
    </script>
</body>

</html>
