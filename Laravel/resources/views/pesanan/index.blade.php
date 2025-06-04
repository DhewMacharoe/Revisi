@extends('layouts.admin')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Menejemen Pesanan - DelBites')

@section('page-title', 'Menejemen Pesanan')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('pesanan.index') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Filter Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>
                                        Menunggu</option>
                                    <option value="pembayaran" {{ request('status') == 'pembayaran' ? 'selected' : '' }}>
                                        Pembayaran</option>
                                    <option value="dibayar" {{ request('status') == 'dibayar' ? 'selected' : '' }}>Dibayar
                                    </option>
                                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>
                                        Diproses</option>
                                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai
                                    </option>
                                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>
                                        Dibatalkan</option>
                                </select>
                            </div>
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
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Reset</a>
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
                        <h5 class="mb-0">Daftar Pesanan</h5>
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
                                            <td>{{ $p->pelanggan->nama }}</td>
                                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                            <td>Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($p->metode_pembayaran == 'tunai')
                                                    <span class="badge bg-success">Tunai</span>
                                                @elseif($p->metode_pembayaran == 'qris')
                                                    <span class="badge bg-info">QRIS</span>
                                                @elseif($p->metode_pembayaran == 'transfer bank')
                                                    <span class="badge bg-primary">Transfer Bank</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->status == 'menunggu')
                                                    <span class="badge bg-warning">Menunggu</span>
                                                @elseif($p->status == 'pembayaran')
                                                    <span class="badge bg-info">Pembayaran</span>
                                                @elseif($p->status == 'dibayar')
                                                    <span class="badge bg-primary">Dibayar</span>
                                                @elseif($p->status == 'diproses')
                                                    <span class="badge bg-secondary">Diproses</span>
                                                @elseif($p->status == 'selesai')
                                                    <span class="badge bg-success">Selesai</span>
                                                @elseif($p->status == 'dibatalkan')
                                                    <span class="badge bg-danger">Dibatalkan</span>
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
                                                                data-id="{{ $p->id }}">
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
                                                            $telepon = preg_replace(
                                                                '/[^0-9]/',
                                                                '',
                                                                $p->pelanggan->telepon,
                                                            );
                                                            if (Str::startsWith($telepon, '0')) {
                                                                $telepon = '62' . substr($telepon, 1);
                                                            }

                                                            $statusText = match ($p->status) {
                                                                'menunggu'
                                                                    => 'Pesanan Anda sedang menunggu konfirmasi.',
                                                                'pembayaran' => 'Silakan segera lakukan pembayaran.',
                                                                'dibayar' => 'Pembayaran Anda telah kami terima.',
                                                                'diproses' => 'Pesanan Anda sedang diproses.',
                                                                'selesai'
                                                                    => 'Pesanan Anda telah selesai. Terima kasih!',
                                                                'dibatalkan' => 'Pesanan Anda telah dibatalkan.',
                                                                default => 'Status pesanan Anda: ' . $p->status,
                                                            };

                                                            $pesan =
                                                                "Halo {$p->pelanggan->nama},\n\nPesanan Anda di *DelBites*:\nTotal: Rp " .
                                                                number_format($p->total_harga, 0, ',', '.') .
                                                                "\nStatus: *" .
                                                                ucfirst($p->status) .
                                                                "*\n\n" .
                                                                $statusText .
                                                                "\n\nTerima kasih telah memesan.";
                                                        @endphp

                                                        <a class="dropdown-item"
                                                            href="https://wa.me/{{ $telepon }}?text={{ urlencode($pesan) }}"
                                                            target="_blank">
                                                            <i class="fab fa-whatsapp me-2"></i> Hubungi Pelanggan
                                                        </a>


                                                        @if (!in_array($p->status, ['selesai', 'dibatalkan']))
                                                            <div class="dropdown-divider"></div>
                                                            <form
                                                                action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'diproses']) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button class="dropdown-item" type="submit"><i
                                                                        class="fas fa-cogs"></i> Tandai Diproses</button>
                                                            </form>
                                                            <form
                                                                action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'selesai']) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button class="dropdown-item" type="submit"><i
                                                                        class="fas fa-check-circle"></i> Tandai
                                                                    Selesai</button>
                                                            </form>
                                                            <form
                                                                action="{{ route('pesanan.status', ['id' => $p->id, 'status' => 'dibatalkan']) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button class="dropdown-item text-danger" type="submit"><i
                                                                        class="fas fa-times-circle"></i> Batalkan</button>
                                                            </form>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data pesanan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $pesanan->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesanan -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pesanan #<span id="pesananId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID Pelanggan:</strong> <span id="idPelanggan"></span></p>
                            <p><strong>Nama Pelanggan:</strong> <span id="namaPelanggan"></span></p>
                            <p><strong>Telepon:</strong> <span id="teleponPelanggan"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Pesanan:</strong> <span id="tanggalPesanan"></span></p>
                            <p><strong>Total:</strong> <span id="totalHarga"></span></p>
                            <p><strong>Metode Pembayaran:</strong> <span id="metodePembayaran"></span></p>
                        </div>
                    </div>

                    <h6 class="mb-3">Daftar Pesanan</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Makanan</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Catatan</th>
                                    <th>Suhu</th>
                                </tr>
                            </thead>
                            <tbody id="detailPesananBody">
                                <!-- Data akan diisi melalui JavaScript -->
                            </tbody>
                        </table>
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
    <script>
        // Script untuk mengambil detail pesanan saat modal dibuka
        document.addEventListener('DOMContentLoaded', function() {
            const detailModal = document.getElementById('detailModal');

            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');

                // Fetch detail pesanan dari server
                fetch(`/pesanan/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // Isi data ke dalam modal
                        document.getElementById('pesananId').textContent = data.id;
                        document.getElementById('idPelanggan').textContent = data.id_pelanggan;
                        document.getElementById('namaPelanggan').textContent = data.pelanggan.nama;
                        document.getElementById('teleponPelanggan').textContent = data.pelanggan
                            .telepon || '-';
                        document.getElementById('tanggalPesanan').textContent = new Date(data
                            .created_at).toLocaleString('id-ID');
                        document.getElementById('totalHarga').textContent = 'Rp ' + new Intl
                            .NumberFormat('id-ID').format(data.total_harga);

                        let metodePembayaran = '';
                        switch (data.metode_pembayaran) {
                            case 'tunai':
                                metodePembayaran = 'Tunai';
                                break;
                            case 'qris':
                                metodePembayaran = 'QRIS';
                                break;
                            case 'transfer bank':
                                metodePembayaran = 'Transfer Bank';
                                break;
                            default:
                                metodePembayaran = data.metode_pembayaran;
                        }
                        document.getElementById('metodePembayaran').textContent = metodePembayaran;

                        // Isi tabel detail pesanan
                        const detailBody = document.getElementById('detailPesananBody');
                        detailBody.innerHTML = '';

                        data.detail_pemesanan.forEach(detail => {
                            const row = document.createElement('tr');

                            const namaMakanan = document.createElement('td');
                            namaMakanan.textContent = detail.menu.nama_menu;

                            const harga = document.createElement('td');
                            harga.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(
                                detail.harga_satuan);

                            const jumlah = document.createElement('td');
                            jumlah.textContent = detail.jumlah;

                            const subtotal = document.createElement('td');
                            subtotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID')
                                .format(detail.subtotal);

                            const catatan = document.createElement('td');
                            catatan.textContent = detail.catatan ||
                            '-'; // tampilkan '-' jika kosong

                            const suhu = document.createElement('td');
                            suhu.textContent = detail.suhu || '-'; // tampilkan '-' jika kosong


                            row.appendChild(namaMakanan);
                            row.appendChild(harga);
                            row.appendChild(jumlah);
                            row.appendChild(subtotal);
                            row.appendChild(catatan); // Tambahkan catatan
                            row.appendChild(suhu); // Tambahkan suhu

                            detailBody.appendChild(row);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengambil data pesanan.');
                    });
            });

            // Auto submit form saat filter berubah
            document.getElementById('status').addEventListener('change', function() {
                this.form.submit();
            });

            document.getElementById('metode_pembayaran').addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
@endsection
