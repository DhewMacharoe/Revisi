@extends('layouts.admin')

@php use Illuminate\Support\Str; @endphp {{-- Ini bisa dihapus jika Str tidak digunakan lagi setelah modifikasi WhatsApp --}}

@section('title', 'Manajemen Pesanan - DelBites')

@section('page-title', 'Manajemen Pesanan')

@section('content')
    <div class="container-fluid">
        {{-- Navigasi Tab untuk Status --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'diproses' ? 'active' : '' }}"
                   href="{{ route('pesanan.index', array_merge(request()->except(['page', 'status']), ['active_tab' => 'diproses'])) }}">
                    Diproses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'selesai' ? 'active' : '' }}"
                   href="{{ route('pesanan.index', array_merge(request()->except(['page', 'status']), ['active_tab' => 'selesai'])) }}">
                    Selesai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'dibatalkan' ? 'active' : '' }}"
                   href="{{ route('pesanan.index', array_merge(request()->except(['page', 'status']), ['active_tab' => 'dibatalkan'])) }}">
                    Dibatalkan
                </a>
            </li>
            {{-- Anda bisa menambahkan tab "Semua" jika diperlukan --}}
            {{-- <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'semua' ? 'active' : '' }}"
                   href="{{ route('pesanan.index', array_merge(request()->except(['page', 'status']), ['active_tab' => 'semua'])) }}">
                    Semua Pesanan
                </a>
            </li> --}}
        </ul>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        {{-- Form Filter --}}
                        <form action="{{ route('pesanan.index') }}" method="GET" class="row g-3">
                            {{-- Input tersembunyi untuk mempertahankan status tab aktif saat filter --}}
                            <input type="hidden" name="active_tab" value="{{ $activeTab }}">

                            <div class="col-md-4">
                                <label for="metode_pembayaran" class="form-label">Filter Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran" class="form-select">
                                    <option value="">Semua Metode</option>
                                    <option value="tunai" {{ request('metode_pembayaran') == 'tunai' ? 'selected' : '' }}>
                                        Tunai</option>
                                    <option value="qris" {{ request('metode_pembayaran') == 'qris' ? 'selected' : '' }}>
                                        QRIS</option>
                                    <option value="transfer bank"
                                        {{ request('metode_pembayaran') == 'transfer bank' ? 'selected' : '' }}>Transfer
                                        Bank</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="menu_search" class="form-label">Cari Nama Menu dalam Pesanan</label>
                                <input type="text" class="form-control" id="menu_search" name="menu_search"
                                       value="{{ request('menu_search') }}" placeholder="Contoh: Ayam Bakar, Es Teh...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('pesanan.index', ['active_tab' => $activeTab]) }}" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pesanan ({{ ucfirst($activeTab) }})</h5>
                        {{-- Tombol Tambah Pesanan jika ada --}}
                        {{-- <a href="{{ route('pesanan.create') }}" class="btn btn-primary">Tambah Pesanan</a> --}}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pesanan as $p)
                                        <tr>
                                            <td>#{{ $p->id }}</td>
                                            <td>{{ $p->pelanggan->nama ?? 'N/A' }}</td>
                                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                            <td>Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($p->metode_pembayaran == 'tunai')
                                                    <span class="badge text-bg-success">Tunai</span>
                                                @elseif($p->metode_pembayaran == 'qris')
                                                    <span class="badge text-bg-info">QRIS</span>
                                                @elseif($p->metode_pembayaran == 'transfer bank')
                                                    <span class="badge text-bg-primary">Transfer Bank</span>
                                                @else
                                                    <span class="badge text-bg-light">{{ ucfirst($p->metode_pembayaran) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->status == 'menunggu')
                                                    <span class="badge text-bg-warning">Menunggu</span>
                                                @elseif($p->status == 'pembayaran')
                                                    <span class="badge text-bg-info">Pembayaran</span>
                                                @elseif($p->status == 'dibayar')
                                                    <span class="badge text-bg-primary">Dibayar</span>
                                                @elseif($p->status == 'diproses')
                                                    <span class="badge text-bg-secondary">Diproses</span>
                                                @elseif($p->status == 'selesai')
                                                    <span class="badge text-bg-success">Selesai</span>
                                                @elseif($p->status == 'dibatalkan')
                                                    <span class="badge text-bg-danger">Dibatalkan</span>
                                                @else
                                                    <span class="badge text-bg-light">{{ ucfirst($p->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" type="button"
                                                        id="dropdownMenuButton{{ $p->id }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $p->id }}">
                                                        <li>
                                                            <a class="dropdown-item detail-btn" href="#"
                                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                                data-id="{{ $p->id }}" data-nama-pelanggan="{{ $p->pelanggan->nama ?? 'N/A' }}"
                                                                data-telepon-pelanggan="{{ $p->pelanggan->telepon ?? '-' }}"
                                                                data-tanggal-pesanan="{{ $p->created_at->format('d M Y, H:i') }}"
                                                                data-total-harga="Rp {{ number_format($p->total_harga, 0, ',', '.') }}"
                                                                data-metode-pembayaran="{{ ucfirst($p->metode_pembayaran) }}">
                                                                <i class="fas fa-eye me-2"></i> Lihat Detail
                                                            </a>
                                                        </li>
                                                        @if (in_array($p->status, ['dibayar', 'diproses', 'selesai']))
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('pesanan.cetak-struk', $p->id) }}"
                                                                    target="_blank">
                                                                    <i class="fas fa-print me-2"></i> Cetak Struk
                                                                </a>
                                                            </li>
                                                        @endif
                                                        @php
                                                            $telepon = preg_replace('/[^0-9]/', '', $p->pelanggan->telepon ?? '');
                                                            if (Str::startsWith($telepon, '0')) {
                                                                $telepon = '62' . substr($telepon, 1);
                                                            } elseif (!Str::startsWith($telepon, '62') && !empty($telepon)) {
                                                                $telepon = '62' . $telepon;
                                                            }


                                                            $statusText = match ($p->status) {
                                                                'menunggu' => 'Pesanan Anda sedang menunggu konfirmasi.',
                                                                'pembayaran' => 'Silakan segera lakukan pembayaran.',
                                                                'dibayar' => 'Pembayaran Anda telah kami terima.',
                                                                'diproses' => 'Pesanan Anda sedang kami proses di dapur.',
                                                                'selesai' => 'Pesanan Anda telah selesai dan siap diambil/diantar. Terima kasih!',
                                                                'dibatalkan' => 'Pesanan Anda telah dibatalkan.',
                                                                default => 'Status pesanan Anda: ' . ucfirst($p->status),
                                                            };

                                                            $pesanWA = "Halo {$p->pelanggan->nama},\n\n";
                                                            $pesanWA .= "Pesanan Anda di *DelBites* dengan ID #{$p->id}:\n";
                                                            $pesanWA .= "Total: Rp " . number_format($p->total_harga, 0, ',', '.') . "\n";
                                                            $pesanWA .= "Status: *" . ucfirst($p->status) . "*\n\n";
                                                            $pesanWA .= $statusText . "\n\n";
                                                            // Daftar menu bisa ditambahkan jika perlu, tapi bisa membuat pesan panjang
                                                            // foreach($p->detail_pemesanan as $item) {
                                                            // $pesanWA .= "- {$item->menu->nama_menu} x {$item->jumlah}\n";
                                                            // }
                                                            $pesanWA .= "Terima kasih telah memesan di DelBites!";
                                                        @endphp
                                                        @if(!empty($telepon))
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="https://wa.me/{{ $telepon }}?text={{ urlencode($pesanWA) }}"
                                                                target="_blank">
                                                                <i class="fab fa-whatsapp me-2"></i> Hubungi Pelanggan
                                                            </a>
                                                        </li>
                                                        @endif

                                                        @if (!in_array($p->status, ['selesai', 'dibatalkan']))
                                                            <li><hr class="dropdown-divider"></li>
                                                            @if($p->status !== 'diproses')
                                                            <li>
                                                                <form action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'diproses']) }}" method="POST" class="form-status-change">
                                                                    @csrf
                                                                    <button class="dropdown-item" type="submit" data-status-baru="Diproses">
                                                                        <i class="fas fa-cogs me-2"></i> Tandai Diproses
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                            @if($p->status !== 'selesai')
                                                            <li>
                                                                <form action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'selesai']) }}" method="POST" class="form-status-change">
                                                                    @csrf
                                                                    <button class="dropdown-item" type="submit" data-status-baru="Selesai">
                                                                        <i class="fas fa-check-circle me-2"></i> Tandai Selesai
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                             @if($p->status !== 'dibatalkan')
                                                            <li>
                                                                <form action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'dibatalkan']) }}" method="POST" class="form-status-change form-batalkan">
                                                                    @csrf
                                                                    <button class="dropdown-item text-danger" type="submit" data-status-baru="Dibatalkan">
                                                                        <i class="fas fa-times-circle me-2"></i> Batalkan Pesanan
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data pesanan untuk status "{{ ucfirst($activeTab) }}"</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $pesanan->links() }} {{-- Pagination links akan otomatis menyertakan parameter filter dari appends() di controller --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable"> {{-- Ditambahkan modal-dialog-scrollable --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pesanan #<span id="pesananIdModal"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailPesananContent">
                        {{-- Konten akan diisi oleh JavaScript --}}
                        <div class="text-center">Memuat data... <i class="fas fa-spinner fa-spin"></i></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@parent {{-- Jika ada script di layout utama yang ingin dipertahankan --}}
{{-- Jika menggunakan CDN SweetAlert dan belum ada di layout utama, uncomment baris di bawah --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto submit form filter pembayaran jika berubah
        const metodePembayaranSelect = document.getElementById('metode_pembayaran');
        if (metodePembayaranSelect) {
            metodePembayaranSelect.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }
        // Untuk menu_search, biarkan pengguna menekan tombol Filter manual karena ini input teks

        // Modal Detail Pesanan
        const detailModalElement = document.getElementById('detailModal');
        const detailPesananContent = document.getElementById('detailPesananContent');

        if (detailModalElement) {
            detailModalElement.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const pesananId = button.getAttribute('data-id');
                document.getElementById('pesananIdModal').textContent = pesananId; // Update ID di judul modal

                // Tampilkan loading
                detailPesananContent.innerHTML = '<div class="text-center">Memuat data... <i class="fas fa-spinner fa-spin"></i></div>';

                fetch(`/pesanan/${pesananId}`) // Pastikan route ini ada dan mengembalikan JSON
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        let detailHtml = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Nama Pelanggan:</strong> ${data.pelanggan.nama || '-'}</p>
                                    <p><strong>Telepon:</strong> ${data.pelanggan.telepon || '-'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Tanggal Pesanan:</strong> ${new Date(data.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                    <p><strong>Total:</strong> Rp ${new Intl.NumberFormat('id-ID').format(data.total_harga)}</p>
                                    <p><strong>Metode Pembayaran:</strong> ${data.metode_pembayaran ? data.metode_pembayaran.charAt(0).toUpperCase() + data.metode_pembayaran.slice(1) : '-'}</p>
                                </div>
                            </div>
                            <h6>Item Pesanan:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th>Harga</th>
                                            <th>Jml</th>
                                            <th>Subtotal</th>
                                            <th>Catatan</th>
                                            <th>Suhu</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        data.detail_pemesanan.forEach(item => {
                            detailHtml += `
                                <tr>
                                    <td>${item.menu ? item.menu.nama_menu : 'Menu Dihapus'}</td>
                                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                                    <td>${item.jumlah}</td>
                                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                    <td>${item.catatan || '-'}</td>
                                    <td>${item.suhu || '-'}</td>
                                </tr>`;
                        });

                        detailHtml += `
                                    </tbody>
                                </table>
                            </div>`;
                        detailPesananContent.innerHTML = detailHtml;
                    })
                    .catch(error => {
                        console.error('Error fetching pesanan detail:', error);
                        detailPesananContent.innerHTML = '<p class="text-danger">Gagal memuat detail pesanan. Silakan coba lagi.</p>';
                    });
            });
        }

        // SweetAlert untuk form perubahan status
        const statusChangeForms = document.querySelectorAll('.form-status-change');
        statusChangeForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const currentForm = this;
                const statusAction = currentForm.action;
                const statusButton = currentForm.querySelector('button[type="submit"]');
                const statusBaru = statusButton.dataset.statusBaru || "status baru"; // ambil dari data-attribute

                let confirmButtonColor = '#3085d6'; // default biru
                let confirmText = `Ya, tandai ${statusBaru}!`;
                let titleText = `Ubah status menjadi "${statusBaru}"?`;
                let textMessage = "Pastikan Anda sudah melakukan tindakan yang sesuai.";

                if (form.classList.contains('form-batalkan')) {
                    confirmButtonColor = '#d33'; // merah untuk batalkan
                    confirmText = 'Ya, batalkan pesanan!';
                    titleText = 'Batalkan Pesanan Ini?';
                    textMessage = "Pesanan yang dibatalkan tidak dapat diubah kembali.";
                }


                // Pastikan Swal ada sebelum memanggilnya
                if (typeof Swal === 'undefined') {
                    console.error('SweetAlert (Swal) is not loaded!');
                    if (confirm(`${titleText}\n${textMessage}`)) { // Fallback ke confirm browser
                        currentForm.submit();
                    }
                    return;
                }

                Swal.fire({
                    title: titleText,
                    text: textMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6c757d', // abu-abu untuk batal
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentForm.submit();
                    }
                });
            });
        });

    });
</script>
@endsection