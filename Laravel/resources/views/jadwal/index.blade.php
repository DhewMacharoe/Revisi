@extends('layouts.admin')

@section('title', 'Jadwal Operasional - DelBites')

@section('page-title', 'Jadwal Operasional')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Kelola Jadwal Operasional</h5>
                        <small class="text-muted">Atur jam buka, jam tutup, atau liburkan toko pada hari tertentu.</small>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.jadwal.update') }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">Hari</th>
                                            <th class="text-center" style="width: 20%;">Status</th>
                                            <th>Jam Buka</th>
                                            <th>Jam Tutup</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($jadwal as $item)
                                            <tr class="schedule-row"
                                                data-is-tutup="{{ $item->is_tutup ? 'true' : 'false' }}">
                                                <td>
                                                    <strong>{{ $item->hari }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="status-{{ $item->id }}"
                                                            name="jadwal[{{ $item->id }}][is_tutup]"
                                                            {{ !$item->is_tutup ? 'checked' : '' }}>
                                                        <label
                                                            class="form-check-label ms-2 status-label">{{ !$item->is_tutup ? 'Buka' : 'Tutup' }}</label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control time-input"
                                                        name="jadwal[{{ $item->id }}][jam_buka]"
                                                        value="{{ \Carbon\Carbon::parse($item->jam_buka)->format('H:i') }}"
                                                        {{ $item->is_tutup ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control time-input"
                                                        name="jadwal[{{ $item->id }}][jam_tutup]"
                                                        value="{{ \Carbon\Carbon::parse($item->jam_tutup)->format('H:i') }}"
                                                        {{ $item->is_tutup ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    Tidak ada data jadwal. Silakan jalankan seeder.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switches = document.querySelectorAll('.form-check-input[type="checkbox"]');
            switches.forEach(function(el) {
                el.addEventListener('change', function() {
                    const row = this.closest('.schedule-row');
                    const label = row.querySelector('.status-label');
                    const timeInputs = row.querySelectorAll('.time-input');

                    if (this.checked) {
                        label.textContent = 'Buka';
                        timeInputs.forEach(input => input.disabled = false);
                    } else {
                        label.textContent = 'Tutup';
                        timeInputs.forEach(input => input.disabled = true);
                    }
                });
            });
        });
    </script>
@endsection
