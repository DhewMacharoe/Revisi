@extends('layouts.auth')

@section('title', 'Register - DelBites')
@section('subtitle', 'Daftar Akun Admin Baru')

@section('styles')
@parent {{-- Penting untuk mempertahankan style dari layout utama jika ada --}}
<style>
    /* CSS untuk menyembunyikan angka pada input number dan menghilangkan tombol spinner */
    .numeric-password {
        -webkit-text-security: disc;
        -moz-text-security: disc;
        text-security: disc;
    }
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
<form method="POST" action="{{ route('register.submit') }}">
    @csrf

    {{-- Input Nama --}}
    <div class="mb-3">
        <label for="nama" class="form-label">Nama</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                   name="nama" value="{{ old('nama') }}" required autofocus>
        </div>
        @error('nama')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Input Email --}}
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                   name="email" value="{{ old('email') }}" required>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Input Password --}}
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                   name="password" required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Input Konfirmasi Password --}}
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                   required>
        </div>
    </div>

    {{-- PERUBAHAN: Input PIN Registrasi --}}
    <div class="mb-3">
        <label for="pin" class="form-label">PIN Registrasi</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-key"></i></span>
            <input type="number" class="form-control numeric-password @error('pin') is-invalid @enderror" id="pin"
                   name="pin" required inputmode="numeric">
        </div>
        @error('pin')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Daftar</button>
    </div>
</form>

<div class="mt-3 text-center">
    <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
</div>
@endsection
<script>
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