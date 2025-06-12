@extends('layouts.admin')

@section('title', 'Dashboard - DelBites')

@section('page-title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Pesanan</h6>
                                <h4 class="mb-0">{{ $totalPesanan }}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-cart text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Pelanggan</h6>
                                <h4 class="mb-0">{{ $totalPelanggan }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Menu</h6>
                                <h4 class="mb-0">{{ $totalMenu }}</h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-box text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Stok Bahan</h6>
                                <h4 class="mb-0">{{ $totalStok }}</h4>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-boxes text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    {{-- [DIUBAH] Menambahkan d-flex dan ikon bantuan --}}
                    <div class="card-header bg-white d-flex align-items-center">
                        <h5 class="mb-0">Pesanan Terbaru</h5>
                        <a tabindex="0" class="ms-2" role="button" data-bs-toggle="popover" data-bs-trigger="focus"
                            title="Informasi Penting"
                            data-bs-content="Pesanan yang sudah diproses tidak dapat dibatalkan kembali.<br><br>Pesanan yang sudah dibatalkan tidak dapat diproses kembali.">
                            <i class="fas fa-info-circle text-muted"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status Pembayaran</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pesananTerbaru as $pesanan)
                                        <tr>
                                            <td>#{{ $pesanan->id }}</td>
                                            <td>{{ $pesanan->pelanggan->nama }}</td>
                                            <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
                                            <td>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($pesanan->status === 'menunggu')
                                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                                @elseif ($pesanan->status === 'pembayaran')
                                                    <span class="badge bg-info text-white">Pembayaran</span>
                                                @elseif ($pesanan->status === 'selesai')
                                                    <span class="badge bg-success ">Selesai</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal"
                                                    data-id="{{ $pesanan->id }}">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>

                                                @if ($pesanan->status === 'menunggu' || $pesanan->status === 'pembayaran')
                                                    <form
                                                        action="{{ route('pesanan.status', ['pesanan' => $pesanan, 'status' => 'diproses']) }}"
                                                        method="POST" class="d-inline form-status-change">
                                                        @csrf
                                                        <input type="hidden" name="status" value="diproses">
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            data-status-baru="Diproses">
                                                            <i class="fas fa-check-circle"></i> Terima
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($pesanan->status !== 'selesai' && $pesanan->status !== 'dibatalkan')
                                                    <form
                                                        action="{{ route('pesanan.status', ['pesanan' => $pesanan, 'status' => 'dibatalkan']) }}"
                                                        method="POST" class="d-inline form-status-change form-batalkan">
                                                        @csrf
                                                        <input type="hidden" name="status" value="dibatalkan">
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            data-status-baru="Dibatalkan">
                                                            <i class="fas fa-times-circle"></i> Batalkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Tidak ada pesanan menunggu
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Menu Terlaris</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Terjual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menuTerlaris as $menu)
                                        <tr>
                                            <td>{{ $menu->nama_menu }}</td>
                                            <td>
                                                @if ($menu->kategori == 'makanan')
                                                    <span class="badge bg-success">Makanan</span>
                                                @else
                                                    <span class="badge bg-info">Minuman</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                                            <td>{{ $menu->total_terjual }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data menu</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pesanan #<span id="pesananId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Konten detail diisi oleh JS --}}
                </div>
                <div class="modal-footer" id="modalFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // [DIUBAH] Inisialisasi Popover Bootstrap
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(
                popoverTriggerEl, {
                    html: true // Izinkan HTML di dalam konten popover
                }));

            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const modalBody = detailModal.querySelector('.modal-body');
                const modalFooter = document.getElementById('modalFooter');

                modalBody.innerHTML =
                    '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
                modalFooter.innerHTML =
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>';

                fetch(`/pesanan/${id}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('pesananId').textContent = data.id;

                        let detailHtml = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Nama Pelanggan:</strong> ${data.pelanggan.nama || '-'}</p>
                                    <p><strong>Telepon:</strong> ${data.pelanggan.telepon || '-'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Tanggal:</strong> ${new Date(data.created_at).toLocaleString('id-ID')}</p>
                                    <p><strong>Total:</strong> Rp ${new Intl.NumberFormat('id-ID').format(data.total_harga)}</p>
                                </div>
                            </div>
                            <h6>Item Pesanan:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead><tr><th>Menu</th><th>Harga</th><th>Jml</th><th>Subtotal</th><th>Catatan</th><th>Suhu</th></tr></thead>
                                    <tbody>
                                        ${data.detail_pemesanan.map(item => `
                                                    <tr>
                                                        <td>${item.menu ? item.menu.nama_menu : 'Menu Dihapus'}</td>
                                                        <td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                                                        <td>${item.jumlah}</td>
                                                        <td>Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                                        <td>${item.catatan || '-'}</td>
                                                        <td>${item.suhu || '-'}</td>
                                                    </tr>
                                                `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                        modalBody.innerHTML = detailHtml;

                        const telepon = (data.pelanggan.telepon || '').replace(/[^0-9]/g, '');
                        if (telepon) {
                            const formattedPhone = telepon.startsWith('0') ? '62' + telepon.substring(
                                1) : telepon;
                            const message =
                                `Halo ${data.pelanggan.nama},\n\nPesanan Anda di DelBites #${data.id} telah kami terima. Terima kasih!`;
                            modalFooter.innerHTML = `
                                <a href="https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}" class="btn btn-success me-auto" target="_blank"><i class="fab fa-whatsapp"></i> Hubungi</a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modalBody.innerHTML = '<p class="text-danger">Gagal memuat detail pesanan.</p>';
                    });
            });

            const statusChangeForms = document.querySelectorAll('.form-status-change');
            statusChangeForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const currentForm = this;
                    const statusButton = currentForm.querySelector('button[type="submit"]');
                    const statusBaru = statusButton.dataset.statusBaru || "status baru";

                    let confirmButtonColor = '#3085d6';
                    let confirmText = `Ya, tandai ${statusBaru}!`;
                    let titleText = `Ubah status menjadi "${statusBaru}"?`;
                    let textMessage = "Aksi ini akan mengubah status pesanan.";
                    let showInput = false;
                    let inputPlaceholder = '';
                    let inputValidator = null;

                    if (form.classList.contains('form-batalkan')) {
                        confirmButtonColor = '#d33';
                        confirmText = 'Ya, batalkan pesanan!';
                        titleText = 'Anda yakin ingin membatalkan?';
                        textMessage = "Pesanan yang dibatalkan tidak dapat dikembalikan.";
                        showInput = true;
                        inputPlaceholder = 'Alasan pembatalan (wajib)';
                        inputValidator = (value) => {
                            if (!value) {
                                return 'Anda harus mengisi alasan pembatalan!';
                            }
                        };
                    }

                    Swal.fire({
                        title: titleText,
                        text: textMessage,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: confirmButtonColor,
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Tidak',
                        input: showInput ? 'text' : null,
                        inputPlaceholder: inputPlaceholder,
                        inputValidator: inputValidator
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (showInput) {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'catatan_pembatalan';
                                hiddenInput.value = result.value;
                                currentForm.appendChild(hiddenInput);
                            }
                            currentForm.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
