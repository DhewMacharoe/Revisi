@extends('layouts.admin')

@section('title', 'Manajemen Menu - DelBites')
@section('page-title', 'Manajemen Menu')

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
                        <form action="{{ route('produk.index') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="kategori" class="form-label">Filter Kategori</label>
                                <select name="kategori" id="kategori" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <option value="makanan" {{ request('kategori') == 'makanan' ? 'selected' : '' }}>Makanan
                                    </option>
                                    <option value="minuman" {{ request('kategori') == 'minuman' ? 'selected' : '' }}>Minuman
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari Menu</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Masukkan nama Menu...">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('produk.index') }}" class="btn btn-secondary">Reset</a>
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
                        <h5 class="mb-0">Daftar Menu</h5>
                        <a href="{{ route('produk.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Tambah Menu
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Menu</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produk as $p)
                                        <tr>
                                            <td>
                                                @if ($p->gambar)
                                                    <img src="{{ asset('storage/' . $p->gambar) }}"
                                                        alt="{{ $p->nama_menu }}" class="img-thumbnail" width="50">
                                                @else
                                                    <img src="{{ asset('icon/no-image.png') }}" alt="No Image"
                                                        class="img-thumbnail" width="50">
                                                @endif
                                            </td>
                                            <td>{{ $p->nama_menu }}</td>
                                            <td>
                                                @if ($p->kategori == 'makanan')
                                                    <span class="badge bg-success">Makanan</span>
                                                @else
                                                    <span class="badge bg-info">Minuman</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                                            <td>{{ $p->stok }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('produk.show', $p->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('produk.edit', $p->id) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    {{-- PERBAIKAN 1: Tambahkan data-attribute untuk nama produk --}}
                                                    <form action="{{ route('produk.destroy', $p->id) }}" method="POST"
                                                        class="d-inline delete-form" data-nama-produk="{{ $p->nama_menu }}">
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
                                            {{-- Pastikan colspan sesuai jumlah header (6) --}}
                                            <td colspan="6" class="text-center">Tidak ada data Menu</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $produk->appends(request()->query())->links() }}
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

    {{-- 3. Script custom Anda untuk konfirmasi hapus --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto submit form saat filter kategori berubah
            const kategoriSelect = document.getElementById('kategori');
            if (kategoriSelect) {
                kategoriSelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }

            // SweetAlert untuk konfirmasi hapus
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    // PERBAIKAN 2: Ambil nama produk dari data-attribute yang sudah kita tambahkan
                    const productName = this.dataset.namaProduk || "Menu ini"; // Fallback ke pesan umum

                    if (typeof Swal === 'undefined') {
                        console.error('SweetAlert (Swal) is not loaded!');
                        if (confirm("Apakah Anda yakin ingin menghapus '" + productName + "'? (SweetAlert gagal dimuat)")) {
                            this.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        // PERBAIKAN 3: Gunakan variabel productName di pesan
                        text: "Menu '" + productName + "' akan dihapus dan tidak dapat dikembalikan!",
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
