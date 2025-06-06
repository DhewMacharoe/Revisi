@extends('layouts.admin')

@section('title', 'Edit Profil - DelBites')
@section('page-title', 'Edit Profil Saya')

{{-- 1. Menyertakan SweetAlert2 CSS via CDN --}}
@section('styles')
    @parent
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profil.update') }}" method="POST" id="editProfileForm">
                        @csrf
                        @method('PUT')

                        {{-- Informasi Dasar --}}
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" 
                                   value="{{ old('nama', $admin->nama ?? auth()->user()->nama) }}"
                                   class="form-control @error('nama') is-invalid @enderror" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $admin->email ?? auth()->user()->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <hr class="my-4">
                        <p class="text-muted mb-1">Ubah Password</p>
                        <small class="form-text text-muted d-block mb-3">Kosongkan jika tidak ingin mengubah password.</small>


                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control @error('password_confirmation') is-invalid @enderror">
                             @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('profil.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveProfileButton">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
{{-- 2. Menyertakan SweetAlert2 JS via CDN (HARUS SEBELUM script custom Anda) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editProfileForm = document.getElementById('editProfileForm');
    const saveProfileButton = document.getElementById('saveProfileButton');

    if (editProfileForm && saveProfileButton) {
        saveProfileButton.addEventListener('click', function (event) {
            event.preventDefault(); // Mencegah submit form standar

            // Cek apakah Swal terdefinisi
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert (Swal) is not loaded! Submitting form directly.');
                editProfileForm.submit(); // Fallback jika Swal tidak ada
                return;
            }

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Apakah Anda yakin ingin menyimpan perubahan pada profil Anda?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tambahkan animasi loading pada tombol (opsional)
                    saveProfileButton.disabled = true;
                    saveProfileButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
                    
                    editProfileForm.submit(); // Submit form jika dikonfirmasi
                }
            });
        });
    }

    // Script untuk menampilkan notifikasi sukses/error dari session (jika controller mengirimkannya)
    // Pastikan controller Anda melakukan redirect dengan session flash jika menggunakan ini.
    @if(session('success_sweetalert'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success_sweetalert') }}",
                timer: 3000,
                showConfirmButton: false
            });
        }
    @endif
    @if(session('error_sweetalert'))
         if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error_sweetalert') }}"
            });
        }
    @endif
});
</script>
@endsection
