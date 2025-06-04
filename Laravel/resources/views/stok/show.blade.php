@extends('layouts.admin')

@section('title', 'Detail Stok Bahan')
@section('page-title', 'Detail Stok Bahan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Detail Stok Bahan
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nama Bahan</th>
                            <td>{{ $stok->nama_bahan }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah</th>
                            <td>{{ $stok->jumlah }}</td>
                        </tr>
                        <tr>
                            <th>Satuan</th>
                            <td>{{ ucfirst($stok->satuan) }}</td>
                        </tr>
                        <tr>
                            <th>Admin Input</th>
                            <td>{{ $stok->admin->nama ?? 'Tidak Diketahui' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $stok->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diubah Terakhir</th>
                            <td>{{ $stok->updated_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('stok.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
