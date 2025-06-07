@extends('layouts.admin')

@section('title', 'Detail Produk - DelBites')
@section('page-title', 'Detail Produk')

{{-- Menyertakan SweetAlert2 CSS via CDN --}}
@section('styles')
    @parent {{-- Opsional: mempertahankan style dari parent layout jika ada --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Kartu Gambar dan Info Singkat --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    @if ($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_menu }}"
                             class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
                    @else
                        <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 200px; width: 100%;">
                            <span class="text-muted">Tidak ada gambar</span>
                        </div>
                    @endif

                    <h4 class="mt-3">{{ $produk->nama_menu }}</h4>
                    <p class="mb-1">
                        @if ($produk->kategori == 'makanan')
                            <span class="badge bg-success">Makanan</span>
                        @else
                            <span class="badge bg-info">Minuman</span>
                        @endif
                    </p>
                    <h5 class="text-primary">Rp {{ number_format($produk->harga, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Produk</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            {{-- ... (detail produk lainnya tetap sama) ... --}}
                            <tr>
                                <th width="200">ID Produk</th>
                                <td>#{{ $produk->id }}</td>
                            </tr>
                            <tr>
                                <th>Nama Produk</th>
                                <td>{{ $produk->nama_menu }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>
                                    @if ($produk->kategori == 'makanan')
                                        <span class="badge bg-success">Makanan</span>
                                    @else
                                        <span class="badge bg-info">Minuman</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Harga</th>
                                <td>Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Stok</th>
                                <td>{{ $produk->stok }}</td>
                            </tr>
                            <tr>
                                <th>Ditambahkan Oleh</th>
                                <td>{{ $produk->admin->nama ?? 'Admin Tidak Diketahui' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Ditambahkan</th>
                                <td>{{ $produk->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diperbarui</th>
                                <td>{{ $produk->updated_at->format('d M Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
                        <a href="{{ route('produk.edit', $produk->id) }}" class="btn btn-warning">Edit</a>
                        {{-- MODIFIKASI FORM HAPUS --}}
                        <form action="{{ route('produk.destroy', $produk->id) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @parent {{-- Opsional: mempertahankan script dari parent layout jika ada --}}

    {{-- 1. Sertakan SweetAlert2 JS via CDN (HARUS SEBELUM script custom Anda) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    {{-- 2. Script custom Anda untuk konfirmasi hapus --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM fully loaded. SweetAlert object:', typeof Swal); // Cek apakah Swal terdefinisi

            const deleteForms = document.querySelectorAll('.delete-form');
            console.log('Found delete forms:', deleteForms.length);

            deleteForms.forEach(form => {
                console.log('Attaching listener to form:', form);
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    console.log('Delete form submit event triggered for form:', this);

                    // Pastikan Swal ada sebelum memanggilnya
                    if (typeof Swal === 'undefined') {
                        console.error('SweetAlert (Swal) is not loaded!');
                        // Fallback ke confirm browser biasa jika Swal tidak ada
                        if (confirm("Produk '" + @json($produk->nama_menu) + "' akan dihapus. Lanjutkan? (SweetAlert gagal dimuat)")) {
                            this.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Produk '" + @json($produk->nama_menu) + "' akan dihapus dan tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log('Confirmed! Submitting form programmatically.');
                            this.submit();
                        } else {
                            console.log('Cancelled by user.');
                        }
                    });
                });
            });
        });
    </script>

    {{-- 3. (Opsional) Script untuk menampilkan notifikasi session (jika tidak pakai realrashid/sweet-alert) --}}
    {{-- Script ini lebih baik diletakkan di layout utama (admin.blade.php) setelah SweetAlert2 JS CDN --}}
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: "{{ session('success') }}",
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    console.warn('SweetAlert (Swal) is not loaded, cannot show session success message.');
                }
            @endif

            @if(session('error'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: "{{ session('error') }}"
                    });
                } else {
                    console.warn('SweetAlert (Swal) is not loaded, cannot show session error message.');
                }
            @endif
            // Tambahkan untuk 'warning' dan 'info' jika diperlukan
        });
    </script>
    --}}
@endsection