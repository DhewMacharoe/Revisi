@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan - DelBites')
@section('page-title', 'Manajemen Pelanggan')

@section('content')
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

        .table th a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }

        .table th a:hover {
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

    <div class="container">
        <h1 class="mb-4">Daftar Pelanggan</h1>

        {{-- Filter dan Search --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('pelanggan.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari Pelanggan</label>
                        <input type="text" name="search" id="search" class="form-control"
                            value="{{ request('search') }}" placeholder="Masukkan nama atau telepon...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Pelanggan --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        <tr class="table-secondary fw-bold">
                            <td> <a
                                    href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'nama', 'sort_order' => $sortBy == 'nama' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                    Nama {!! $sortBy == 'nama' ? ($sortOrder == 'asc' ? '↑' : '↓') : '' !!}
                                </a></td>
                            <td> <a
                                    href="{{ route('pelanggan.index', array_merge(request()->all(), ['sort_by' => 'telepon', 'sort_order' => $sortBy == 'telepon' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                    Telepon {!! $sortBy == 'telepon' ? ($sortOrder == 'asc' ? '↑' : '↓') : '' !!}
                                </a></td>
                            <td>Aksi</td>
                        </tr>
                        @forelse ($pelanggan as $p)
                            <tr>
                                <td>{{ $p->nama }}</td>
                                <td>{{ $p->telepon }}</td>
                                <td>
                                    <form action="{{ route('pelanggan.destroy', $p->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $pelanggan->links() }}
        </div>
    </div>
@endsection
