@extends('layouts.admin')

@section('title', 'Edit Stok Bahan')
@section('page-title', 'Edit Stok Bahan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.update', $stok->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nama_bahan" class="form-label">Nama Bahan</label>
                            <input type="text" name="nama_bahan" id="nama_bahan" class="form-control" required 
                                   value="{{ old('nama_bahan', $stok->nama_bahan) }}" placeholder="Contoh: Gula Pasir">
                        </div>

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" id="jumlah" class="form-control" required min="1" 
                                   value="{{ old('jumlah', $stok->jumlah) }}" placeholder="Contoh: 5">
                        </div>

                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan</label>
                            <select name="satuan" id="satuan" class="form-select" required>
                                <option value="" disabled>-- Pilih Satuan --</option>
                                @foreach (['kg', 'liter', 'pcs', 'tandan', 'dus'] as $satuan)
                                    <option value="{{ $satuan }}" {{ old('satuan', $stok->satuan) == $satuan ? 'selected' : '' }}>
                                        {{ ucfirst($satuan) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('stok.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
