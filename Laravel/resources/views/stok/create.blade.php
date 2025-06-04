@extends('layouts.admin')

@section('title', 'Tambah Stok Bahan')
@section('page-title', 'Tambah Stok Bahan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nama_bahan" class="form-label">Nama Bahan</label>
                            <input type="text" name="nama_bahan" id="nama_bahan" class="form-control" 
                                   required placeholder="Contoh: Gula Pasir">
                        </div>

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" id="jumlah" class="form-control" 
                                   required min="1" placeholder="Contoh: 5">
                        </div>

                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan</label>
                            <select name="satuan" id="satuan" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                                <option value="kg">Kg</option>
                                <option value="liter">Liter</option>
                                <option value="pcs">Pcs</option>
                                <option value="tandan">Tandan</option>
                                <option value="dus">Dus</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <a href="{{ route('stok.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
