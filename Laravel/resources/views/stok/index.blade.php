@extends('layouts.admin')

@section('title', 'Stok Bahan - DelBites')

@section('page-title', 'Stok Bahan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="satuan" class="form-label">Filter Satuan</label>
                            <select name="satuan" id="satuan" class="form-select">
                                <option value="">Semua Satuan</option>
                                <option value="kg" {{ request('satuan') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                <option value="liter" {{ request('satuan') == 'liter' ? 'selected' : '' }}>Liter</option>
                                <option value="pcs" {{ request('satuan') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                <option value="tandan" {{ request('satuan') == 'tandan' ? 'selected' : '' }}>Tandan</option>
                                <option value="dus" {{ request('satuan') == 'dus' ? 'selected' : '' }}>Dus</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Cari Bahan</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Masukkan nama bahan...">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('stok.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Stok Bahan</h5>
                    <a href="{{ route('stok.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Stok
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Bahan</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Ditambahkan Oleh</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stok as $s)
                                <tr>
                                    <td>#{{ $s->id }}</td>
                                    <td>{{ $s->nama_bahan }}</td>
                                    <td>{{ $s->jumlah }}</td>
                                    <td>{{ ucfirst($s->satuan) }}</td>
                                    <td>{{ $s->admin->nama }}</td>
                                    <td>{{ $s->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('stok.show', $s->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('stok.edit', $s->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('stok.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus stok bahan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data stok bahan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $stok->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto submit form saat filter berubah
    document.getElementById('satuan').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endsection