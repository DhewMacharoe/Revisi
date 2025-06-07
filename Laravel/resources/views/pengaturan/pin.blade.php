@extends('layouts.admin')

@section('title', 'Manajemen PIN Registrasi - DelBites')
@section('page-title', 'Manajemen PIN Registrasi')

@section('styles')
    @parent
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    {{-- CSS untuk menyembunyikan angka pada input number --}}
    <style>
        .numeric-password {
            -webkit-text-security: disc;
            -moz-text-security: disc;
            text-security: disc;
        }
        /* Mencegah tombol spinner pada input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
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
                    <p class="text-muted">PIN harus berupa angka dan akan digunakan sebagai syarat untuk mendaftarkan akun admin baru.</p>
                    
                    <form action="{{ route('pin.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN Baru (Hanya Angka, 6 digit)</label>
                            {{-- Mengubah tipe input dan menambahkan class --}}
                            <input type="number" name="pin" id="pin"
                                   class="form-control numeric-password @error('pin') is-invalid @enderror" 
                                   inputmode="numeric" required>
                            @error('pin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pin_confirmation" class="form-label">Konfirmasi PIN Baru</label>
                            <input type="number" name="pin_confirmation" id="pin_confirmation"
                                   class="form-control numeric-password" 
                                   inputmode="numeric" required>
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
                    Terakhir diperbarui pada: {{ $pinSetting->updated_at->format('d M Y, H:i') }}
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

        document.addEventListener('DOMContentLoaded', function() {
        // Pilih semua input PIN
        const pinInputs = document.querySelectorAll('input[name="pin"], input[name="pin_confirmation"]');
        
        pinInputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                // Blokir input jika tombol yang ditekan adalah 'e', '+', atau '-'
                if (e.key === 'e' || e.key === '+' || e.key === '-') {
                    e.preventDefault();
                }
            });
        });
    });
</script>

@endsection
