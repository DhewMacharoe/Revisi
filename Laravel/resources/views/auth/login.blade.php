@extends('layouts.auth')

@section('title', 'Login - DelBites')

@section('subtitle', 'Masuk ke Akun Admin')

<div class="col-md-6">
    @section('content')
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

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
            {{-- Uncomment if you want to add a "Remember Me" checkbox --}}
            
            {{-- <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div> --}}

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>
    </div>
    </form>

    <div class="mt-3 text-center">
        <p>Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </div>
@endsection
