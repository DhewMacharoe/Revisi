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
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Pesanan Terbaru</h5>
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
                                                    {{-- [DIUBAH] Menambahkan class untuk SweetAlert --}}
                                                    <form
                                                        action="{{ route('pesanan.status', ['pesanan' => $pesanan->id, 'status' => 'diproses']) }}"
                                                        method="POST" class="d-inline form-status-change">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            data-status-baru="Diproses">
                                                            <i class="fas fa-check-circle"></i> Terima
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- [BARU] Menambahkan tombol Batalkan sesuai permintaan --}}
                                                @if ($pesanan->status !== 'selesai' && $pesanan->status !== 'dibatalkan')
                                                    <form
                                                        action="{{ route('pesanan.status', ['pesanan' => $pesanan->id, 'status' => 'dibatalkan']) }}"
                                                        method="POST" class="d-inline form-status-change form-batalkan">
                                                        @csrf
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
                                <thead>
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
                            <p><strong>Status:</strong> <span id="statusPesanan"></span></p>
                        </div>
                    </div>

                    <h6 class="mb-3">Daftar Pesanan</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Menu</th>
                                    <th>Harga</th>
                                    <th>Jumlah </th>
                                    <th>Subtotal</th>
                                    <th>Suhu</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="detailPesananBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer" id="modalFooter">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- [BARU] Menambahkan link & script SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script untuk modal detail (Tidak diubah)
            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const modalFooter = document.getElementById('modalFooter');
                modalFooter.innerHTML =
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>';
                fetch(`/pesanan/${id}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('pesananId').textContent = data.id;
                        document.getElementById('idPelanggan').textContent = data.id_pelanggan;
                        document.getElementById('namaPelanggan').textContent = data.pelanggan.nama;
                        document.getElementById('teleponPelanggan').textContent = data.pelanggan
                            .telepon || '-';
                        document.getElementById('tanggalPesanan').textContent = new Date(data
                            .created_at).toLocaleString('id-ID');
                        document.getElementById('totalHarga').textContent = 'Rp ' + new Intl
                            .NumberFormat('id-ID').format(data.total_harga);
                        const paymentMethods = {
                            'tunai': 'Tunai',
                            'qris': 'QRIS',
                            'transfer bank': 'Transfer Bank'
                        };
                        document.getElementById('metodePembayaran').textContent = paymentMethods[data
                            .metode_pembayaran] || data.metode_pembayaran;
                        const statusBadges = {
                            'menunggu': {
                                class: 'bg-warning',
                                text: 'Menunggu'
                            },
                            'pembayaran': {
                                class: 'bg-info',
                                text: 'Pembayaran'
                            },
                            'dibayar': {
                                class: 'bg-primary',
                                text: 'Dibayar'
                            },
                            'diproses': {
                                class: 'bg-secondary',
                                text: 'Diproses'
                            },
                            'selesai': {
                                class: 'bg-success',
                                text: 'Selesai'
                            },
                            'dibatalkan': {
                                class: 'bg-danger',
                                text: 'Dibatalkan'
                            }
                        };
                        const status = statusBadges[data.status] || {
                            class: 'bg-secondary',
                            text: data.status
                        };
                        document.getElementById('statusPesanan').innerHTML =
                            `<span class="badge ${status.class}">${status.text}</span>`;
                        const detailBody = document.getElementById('detailPesananBody');
                        detailBody.innerHTML = data.detail_pemesanan.map(detail => `
                            <tr>
                                <td>${detail.menu ? detail.menu.nama_menu : 'Menu Dihapus'}</td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(detail.harga_satuan)}</td>
                                <td>${detail.jumlah}</td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(detail.subtotal)}</td>
                                <td>${detail.suhu || '-'}</td>
                                <td>${detail.catatan || '-'}</td>
                            </tr>
                        `).join('');
                        const telepon = data.pelanggan.telepon.replace(/[^0-9]/g, '');
                        const formattedPhone = telepon.startsWith('0') ? '62' + telepon.substring(1) :
                            telepon;
                        const statusMessages = {
                            'menunggu': 'Pesanan Anda sedang menunggu konfirmasi.',
                            'pembayaran': 'Silakan segera lakukan pembayaran.',
                            'dibayar': 'Pembayaran Anda telah kami terima.',
                            'diproses': 'Pesanan Anda sedang diproses.',
                            'selesai': 'Pesanan Anda telah selesai. Terima kasih!',
                            'dibatalkan': 'Pesanan Anda telah dibatalkan.'
                        };
                        const message =
                            `Halo ${data.pelanggan.nama},\n\nPesanan Anda di *DelBites*:\nTotal: Rp ${ new Intl.NumberFormat('id-ID').format(data.total_harga)}\nStatus: *${status.text}*\n\n${statusMessages[data.status] || `Status pesanan Anda: ${data.status}`}\n\nTerima kasih telah memesan.`;
                        modalFooter.innerHTML = `
                            <a href="https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}" 
                               class="btn btn-success me-2" target="_blank">
                                <i class="fab fa-whatsapp"></i> Hubungi Pelanggan
                            </a>
                        `;
                        modalFooter.innerHTML +=
                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengambil data pesanan.');
                    });
            });

            // Script window.updateStatus (Tidak diubah, dibiarkan seperti aslinya)
            window.updateStatus = function(pesananId, status) {
                fetch(`/pesanan/status/${pesananId}/${status}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(detailModal);
                            modal.hide();
                            window.location.reload();
                        } else {
                            alert('Gagal memperbarui status pesanan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memperbarui status pesanan');
                    });
            }

            // [BARU] Script untuk konfirmasi SweetAlert dan input catatan pembatalan
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
                    let textMessage = "Pastikan Anda sudah melakukan tindakan yang sesuai.";
                    let showInput = false;
                    let inputPlaceholder = '';
                    let inputValidator = null;

                    if (form.classList.contains('form-batalkan')) {
                        confirmButtonColor = '#d33';
                        confirmText = 'Ya, batalkan pesanan!';
                        titleText = 'Batalkan Pesanan Ini?';
                        textMessage = "Pesanan yang dibatalkan tidak dapat diubah kembali.";
                        showInput = true;
                        inputPlaceholder = 'Alasan pembatalan (wajib)';
                        inputValidator = (value) => {
                            if (!value) {
                                return 'Alasan pembatalan tidak boleh kosong!';
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
                                hiddenInput.name =
                                    'catatan_pembatalan';
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
