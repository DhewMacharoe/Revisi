@extends('layouts.admin')

@section('title', 'Notifikasi')
@section('page-title', 'Halaman Notifikasi')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Notifikasi</h5>
    </div>
    <div class="card-body">
        @if ($notifikasi->count())
            <ul id="daftar-notifikasi" class="list-group">
                @foreach ($notifikasi as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $item->judul }}</strong><br>
                            <small class="text-muted">{{ $item->pesan }}</small>
                        </div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">Tidak ada notifikasi untuk ditampilkan.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    window.Echo.channel('notifikasi-channel')
        .listen('.notifikasi-event', (e) => {
            const data = e.pesan;
            const list = document.getElementById('daftar-notifikasi');

            // Buat elemen baru
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-start';

            const content = `
                <div>
                    <strong>${data.judul}</strong><br>
                    <small class="text-muted">${data.pesan}</small>
                </div>
                <small class="text-muted">Baru saja</small>
            `;

            li.innerHTML = content;

            // Sisipkan ke atas daftar
            if (list) {
                list.prepend(li);
            } else {
                console.warn("Elemen daftar-notifikasi tidak ditemukan.");
            }

            // Optional: tampilkan alert atau suara
            // alert(`Notifikasi baru: ${data.judul}`);
        });
</script>
@endpush
