@extends('layouts.admin')

@section('title', 'Manajemen PIN Registrasi - DelBites')
@section('page-title', 'Manajemen PIN Registrasi')

@section('styles')
    @parent
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Atur PIN Registrasi Admin</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">PIN ini akan digunakan sebagai syarat untuk mendaftarkan akun admin baru. Harap simpan PIN ini dengan aman.</p>
                    
                    <form action="{{ route('pin.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN Baru (Minimal 6 karakter)</label>
                            <input type="password" name="pin" id="pin"
                                   class="form-control @error('pin') is-invalid @enderror" required>
                            @error('pin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pin_confirmation" class="form-label">Konfirmasi PIN Baru</label>
                            <input type="password" name="pin_confirmation" id="pin_confirmation"
                                   class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                             <a href="{{ route('profil.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Profil
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan PIN
                            </button>
                        </div>
                    </form>
                </div>
                @if($pinSetting->updated_at && $pinSetting->value)
                <div class="card-footer bg-light text-muted text-sm">
                    PIN terakhir diperbarui pada: {{ $pinSetting->updated_at->format('d M Y, H:i') }}
                    @if($pinSetting->updatedByAdmin)
                        oleh {{ $pinSetting->updatedByAdmin->nama }}
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success_sweetalert'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success_sweetalert') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    });
</script>
@endsection
