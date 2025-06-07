@extends('layouts.admin')

@section('title', 'Stok Bahan - DelBites')
@section('page-title', 'Stok Bahan')

{{-- 1. Menyertakan SweetAlert2 CSS via CDN --}}
@section('styles')
    @parent {{-- Opsional: mempertahankan style dari parent layout jika ada --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

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
                                    <td>{{ $s->admin->nama ?? 'N/A' }}</td>
                                    <td>{{ $s->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('stok.show', $s->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('stok.edit', $s->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {{-- PERBAIKAN 1: Menambahkan data-nama-bahan untuk dibaca oleh JavaScript --}}
                                            <form action="{{ route('stok.destroy', $s->id) }}" method="POST" class="d-inline delete-form" data-nama-bahan="{{ $s->nama_bahan }}">
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
@parent

{{-- 2. Sertakan SweetAlert2 JS via CDN (HARUS SEBELUM script custom Anda) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

{{-- 3. Script custom Anda --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit form saat filter satuan berubah
    const satuanSelect = document.getElementById('satuan');
    if (satuanSelect) {
        satuanSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // SweetAlert untuk konfirmasi hapus
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // PERBAIKAN 2: Ambil nama bahan dari data-attribute yang sudah dititipkan
            const namaBahan = this.dataset.namaBahan || "Stok bahan ini";

            if (typeof Swal === 'undefined') {
                console.error('SweetAlert (Swal) is not loaded!');
                if (confirm("Apakah Anda yakin ingin menghapus '" + namaBahan + "'? (SweetAlert gagal dimuat)")) {
                    this.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Apakah Anda yakin?',
                // PERBAIKAN 3: Gunakan variabel namaBahan di dalam pesan
                text: "Stok bahan '" + namaBahan + "' akan dihapus dan tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});
</script>

@endsection
