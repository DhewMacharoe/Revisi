@extends('layouts.admin')

@section('title', 'Edit Produk - DelBites')

@section('page-title', 'Edit Produk')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="nama_menu" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control @error('nama_menu') is-invalid @enderror"
                                    id="nama_menu" name="nama_menu" value="{{ old('nama_menu', $produk->nama_menu) }}"
                                    required>
                                @error('nama_menu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select @error('kategori') is-invalid @enderror" id="kategori"
                                    name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="makanan"
                                        {{ old('kategori', $produk->kategori) == 'makanan' ? 'selected' : '' }}>Makanan
                                    </option>
                                    <option value="minuman"
                                        {{ old('kategori', $produk->kategori) == 'minuman' ? 'selected' : '' }}>Minuman
                                    </option>
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('harga') is-invalid @enderror"
                                        id="harga" name="harga" value="{{ old('harga', $produk->harga) }}" required>
                                </div>
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control @error('stok') is-invalid @enderror"
                                    id="stok" name="stok" value="{{ old('stok', $produk->stok) }}" required>
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Produk</label>
                                @if ($produk->gambar)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/menu/' . $produk->gambar) }}"
                                            alt="{{ $produk->nama_menu }}" class="img-thumbnail" width="150">
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                    id="gambar" name="gambar">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB. Biarkan kosong jika tidak
                                    ingin mengubah gambar.</small>
                                @error('gambar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('produk.index') }}" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
