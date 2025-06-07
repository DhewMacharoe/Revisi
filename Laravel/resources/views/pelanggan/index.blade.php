@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan - DelBites')
@section('page-title', 'Manajemen Pelanggan')

@section('content')
    {{-- Gaya kustom dari file Anda dipertahankan --}}
    <style>
        a{
            color: #000;
            text-decoration: none; 
        }
        .container {
            padding: 20px;
        }
        .card {
            border-radius: 10px;
        }
        .card-body {
            padding: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn {
            border-radius: 8px;
        }
        .table th,
        .table td {
            vertical-align: middle;
        }
        .table thead th a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }
        .table thead th a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .pagination {
            justify-content: center;
        }
        .table thead {
            background-color: #f8f9fa;
        }
        .btn-sm {
            padding: 4px 10px;
        }
    </style>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Daftar Pelanggan</h1>

        {{-- Filter dan Search --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('pelanggan.index') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Cari Pelanggan</label>
                        <input type="text" name="search" id="search" class="form-control"
                            value="{{ request('search') }}" placeholder="Masukkan nama, email, atau telepon...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Pelanggan yang Diperbarui --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'id', 'sort_order' => $sortBy == 'id' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                        ID {!! $sortBy == 'id' ? ($sortOrder == 'asc' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>') : '' !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'nama', 'sort_order' => $sortBy == 'nama' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                        Nama {!! $sortBy == 'nama' ? ($sortOrder == 'asc' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>') : '' !!}
                                    </a>
                                </th>
                                <th>
                                     <a href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'email', 'sort_order' => $sortBy == 'email' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                        Email {!! $sortBy == 'email' ? ($sortOrder == 'asc' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>') : '' !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'telepon', 'sort_order' => $sortBy == 'telepon' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                        Telepon {!! $sortBy == 'telepon' ? ($sortOrder == 'asc' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>') : '' !!}
                                    </a>
                                </th>
                                 <th>
                                     <a href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'created_at', 'sort_order' => $sortBy == 'created_at' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                        Tanggal Bergabung {!! $sortBy == 'created_at' ? ($sortOrder == 'asc' ? '<i class="fas fa-sort-up ms-1"></i>' : '<i class="fas fa-sort-down ms-1"></i>') : '' !!}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pelanggan as $p)
                                <tr>
                                    <td>#{{ $p->id }}</td>
                                    <td>{{ $p->nama }}</td>
                                    <td>{{ $p->email ?? '-' }}</td>
                                    <td>{{ $p->telepon }}</td>
                                    <td>{{ $p->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data pelanggan yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($pelanggan->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>
@endsection
