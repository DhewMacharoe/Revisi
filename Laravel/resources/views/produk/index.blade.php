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
                                                    {{-- Form Hapus dengan class .delete-form --}}
                                                    <form action="{{ route('produk.destroy', $p->id) }}" method="POST"
                                                        class="d-inline delete-form">
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
                                            <td colspan="6" class="text-center">Tidak ada data produk</td>
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
    @parent {{-- Opsional: mempertahankan script dari parent layout jika ada --}}

    {{-- 2. Sertakan SweetAlert2 JS via CDN (HARUS SEBELUM script custom Anda) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    {{-- 3. Script custom Anda untuk konfirmasi hapus --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded. SweetAlert object:', typeof Swal); // Cek apakah Swal terdefinisi

            // Auto submit form saat filter kategori berubah
            const kategoriSelect = document.getElementById('kategori');
            if (kategoriSelect) {
                kategoriSelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }

            // SweetAlert untuk konfirmasi hapus
            const deleteForms = document.querySelectorAll('.delete-form');
            console.log('Found delete forms on index page:', deleteForms.length);

            deleteForms.forEach(form => {
                console.log('Attaching listener to form (index page):', form);
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    console.log('Delete form submit event triggered for form (index page):', this);

                    if (typeof Swal === 'undefined') {
                        console.error('SweetAlert (Swal) is not loaded on index page!');
                        // Ambil nama produk dari elemen terdekat jika memungkinkan, atau pesan generik
                        let productName = 'Produk ini'; // Pesan default
                        // Anda bisa coba mengambil nama produk dari kolom tabel jika strukturnya memungkinkan
                        // Contoh (perlu disesuaikan dengan struktur HTML Anda):
                        // const row = this.closest('tr');
                        // if (row) {
                        //     const nameCell = row.querySelector('td:nth-child(2)'); // Asumsi nama produk di kolom kedua
                        //     if (nameCell) productName = "'" + nameCell.textContent.trim() + "'";
                        // }

                        if (confirm(productName + " akan dihapus. Lanjutkan? (SweetAlert gagal dimuat)")) {
                            this.submit();
                        }
                        return;
                    }

                    // Dapatkan nama produk. Karena kita di dalam loop, kita tidak bisa langsung pakai @json($p->nama_menu)
                    // Salah satu cara adalah dengan menambahkan data attribute ke tombol atau form.
                    // Untuk kesederhanaan, kita gunakan pesan umum di sini atau Anda bisa implementasikan data attribute.
                    // Contoh jika Anda menambahkan data-nama="{{ $p->nama_menu }}" pada form:
                    // const productName = this.dataset.nama || "Produk ini";
                    const productName = "Produk ini"; // Pesan umum untuk contoh ini

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: productName + " akan dihapus dan tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log('Confirmed (index page)! Submitting form programmatically.');
                            this.submit();
                        } else {
                            console.log('Cancelled by user (index page).');
                        }
                    });
                });
            });
        });
    </script>

    {{-- 4. (Opsional) Script untuk menampilkan notifikasi session (jika tidak pakai realrashid/sweet-alert) --}}
    {{-- Jika Anda menggunakan pendekatan CDN ini secara menyeluruh, Anda perlu script seperti ini di layout utama --}}
    {{-- (Lihat contoh di respons sebelumnya untuk produk.show.blade.php) --}}
@endsection