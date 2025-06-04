@extends('layouts.admin')

@section('title', 'Manajemen Menu - DelBites')
@section('page-title', 'Manajemen Menu')

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
                                                    <form action="{{ route('produk.destroy', $p->id) }}" method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
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
                                            <td colspan="7" class="text-center">Tidak ada data produk</td>
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
    <script>
        // Auto submit form saat filter berubah
        document.getElementById('kategori').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
@endsection
