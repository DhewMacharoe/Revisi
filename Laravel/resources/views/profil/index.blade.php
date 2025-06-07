@extends('layouts.admin')

@section('title', 'Profil Saya - DelBites')
@section('page-title', 'Profil Saya')

@section('content')
<div class="container-fluid">
    {{-- Notifikasi Sukses dari session --}}
    @if (session('success_sweetalert'))
        {{-- Penanganan notifikasi ini akan lebih baik jika ada di layout utama atau di section scripts --}}
    @endif

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Profil</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="profileActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bars me-1"></i> Pengaturan
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileActionsDropdown">
                            <li><a class="dropdown-item" href="{{ route('profil.edit') }}"><i class="fas fa-edit me-2"></i>Edit Profil</a></li>
                            

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('pin.edit') }}"><i class="fas fa-key me-2"></i>Kelola PIN Registrasi</a></li>

                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- <div class="col-md-4 text-center mb-3 mb-md-0">
                            <img src="{{ $admin->foto ? asset('storage/' . $admin->foto) : asset('images/default-avatar.png') }}"
                                 alt="Foto Profil {{ $admin->nama }}"
                                 class="img-fluid rounded-circle shadow-sm"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div> --}}
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4">Nama</dt>
                                <dd class="col-sm-8">{{ $admin->nama ?? 'Tidak Ada Nama' }}</dd>

                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">{{ $admin->email ?? 'Tidak Ada Email' }}</dd>

                                <dt class="col-sm-4">Bergabung Sejak</dt>
                                <dd class="col-sm-8">{{ $admin->created_at ? $admin->created_at->format('d F Y') : '-' }}</dd>

                                <dt class="col-sm-4">Terakhir Diperbarui</dt>
                                <dd class="col-sm-8">{{ $admin->updated_at ? $admin->updated_at->diffForHumans() : '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end">
                     <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
{{-- Jika Anda menggunakan SweetAlert untuk notifikasi dari session, pastikan skrip ini ada setelah SweetAlert JS dimuat --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script> --}}
{{-- <script> --}}
{{-- document.addEventListener('DOMContentLoaded', function() { --}}
{{-- @if(session('success_sweetalert')) --}}
{{-- Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success_sweetalert') }}", timer:3000, showConfirmButton: false }); --}}
{{-- @endif --}}
{{-- }); --}}
{{-- </script> --}}
@endsection
