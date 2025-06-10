@extends('layouts.admin')

@section('title', 'Tambah Menu - DelBites')

@section('page-title', 'Tambah Menu')

@section('content')
<style>
    /* CSS untuk menghilangkan panah pada input angka */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        display: none; /* Aturan paling penting */
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield; /* Untuk Firefox */
    }
</style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Form Tambah Menu</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_menu" class="form-label">Nama Menu <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nama_menu') is-invalid @enderror"
                                            id="nama_menu" name="nama_menu" value="{{ old('nama_menu') }}" required>
                                        @error('nama_menu')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="kategori" class="form-label">Kategori <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('kategori') is-invalid @enderror" id="kategori"
                                            name="kategori" required>
                                            <option value="" selected disabled>Pilih Kategori</option>
                                            <option value="makanan" {{ old('kategori') == 'makanan' ? 'selected' : '' }}>
                                                Makanan</option>
                                            <option value="minuman" {{ old('kategori') == 'minuman' ? 'selected' : '' }}>
                                                Minuman</option>
                                        </select>
                                        @error('kategori')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga (Rp) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('harga') is-invalid @enderror"
                                            id="harga" name="harga" value="{{ old('harga') }}" min="0"
                                            required>
                                        @error('harga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('stok') is-invalid @enderror"
                                            id="stok" name="stok" value="{{ old('stok') }}" min="0"
                                            required>
                                        @error('stok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar Menu</label>
                                        <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                            id="gambar" name="gambar" accept="image/jpeg,image/png,image/jpg">
                                        <small class="text-muted">Format: JPG, JPEG, PNG. Maks: 2MB</small>
                                        @error('gambar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mt-3">
                                        <div id="preview-container" class="d-none">
                                            <label class="form-label">Preview Gambar:</label>
                                            <div class="border rounded p-2">
                                                <img id="preview-image" src="#" alt="Preview"
                                                    class="img-fluid rounded">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>  
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Preview gambar sebelum upload
        document.getElementById('gambar').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('preview-container');
            const previewImage = document.getElementById('preview-image');

            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                previewContainer.classList.add('d-none');
            }
        });
    </script>
@endsection
