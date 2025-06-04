@extends('layouts.admin')

@section('title', 'Menejemen Laporan - DelBites')

@section('page-title', 'Menejemen Laporan')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        .report-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .report-card .icon {
            width: 48px;
            height: 48px;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d6efd;
        }

        .report-card .icon i {
            font-size: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="h3">Laporan Pendapatan</h1>
                <p class="text-muted">Lihat dan ekspor laporan pendapatan</p>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center">
                <div class="dropdown me-2">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Ekspor
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('reports.export.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="period" class="form-label">Periode</label>
                        <select class="form-select" id="period" name="period" onchange="this.form.submit()">
                            <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Harian</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date_range" class="form-label">Rentang Tanggal</label>
                        <div class="input-group">
                            <input type="text" class="form-control date-range-picker" id="date_range"
                                placeholder="Pilih rentang tanggal">
                            <input type="hidden" name="start_date" id="start_date"
                                value="{{ $startDate->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card report-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title mb-0">Total Pendapatan</h6>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <h2 class="mb-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h2>
                        <div class="d-flex align-items-center">
                            <span class="text-muted small">
                                {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card report-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title mb-0">Total Pesanan</h6>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <h2 class="mb-1">{{ $totalOrders }}</h2>
                        <div class="d-flex align-items-center">
                            <span class="text-muted small">
                                {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Reports -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Laporan {{ $period == 'daily' ? 'Harian' : 'Bulanan' }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th class="text-end">Total Pesanan</th>
                                <th class="text-end">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report['period'] }}</td>
                                    <td class="text-end">{{ $report['total_orders'] }}</td>
                                    <td class="text-end">Rp {{ number_format($report['total_income'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">Tidak ada data laporan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Orders -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detail Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>#ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $order->pelanggan->nama ?? '-' }}</td>
                                    <td>
                                        @switch($order->status)
                                            @case('menunggu')
                                                <span class="badge bg-warning">Menunggu</span>
                                            @break

                                            @case('dibayar')
                                                <span class="badge bg-info">Dibayar</span>
                                            @break

                                            @case('diproses')
                                                <span class="badge bg-primary">Diproses</span>
                                            @break

                                            @case('selesai')
                                                <span class="badge bg-success">Selesai</span>
                                            @break

                                            @case('dibatalkan')
                                                <span class="badge bg-danger">Dibatalkan</span>
                                            @break
                                        @endswitch
                                    </td>
                                    <td class="text-end">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('reports.receipt', ['pesanan' => $order->id]) }}"
                                            class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-receipt"></i> Struk
                                        </a>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#orderDetailModal{{ $order->id }}">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Detail Modals -->
        @foreach ($orders as $order)
            <!-- Order Detail Modal for Order #{{ $order->id }} -->
            <div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1"
                aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="orderDetailModalLabel{{ $order->id }}">Detail Pesanan
                                #ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <!-- Informasi Pesanan -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Informasi Pesanan</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th style="width: 40%">ID Pelanggan</th>
                                                        <td>CUST-{{ str_pad($order->id_pelanggan, 3, '0', STR_PAD_LEFT) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Nama Pelanggan</th>
                                                        <td>{{ $order->pelanggan->nama }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total</th>
                                                        <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status</th>
                                                        <td>
                                                            @if ($order->status == 'menunggu')
                                                                <span class="badge bg-warning">Menunggu</span>
                                                            @elseif($order->status == 'pembayaran')
                                                                <span class="badge bg-warning text-dark">Pembayaran</span>
                                                            @elseif($order->status == 'dibayar')
                                                                <span class="badge bg-info">Dibayar</span>
                                                            @elseif($order->status == 'diproses')
                                                                <span class="badge bg-primary">Diproses</span>
                                                            @elseif($order->status == 'selesai')
                                                                <span class="badge bg-success">Selesai</span>
                                                            @elseif($order->status == 'dibatalkan')
                                                                <span class="badge bg-danger">Dibatalkan</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Metode Pembayaran</th>
                                                        <td>{{ $order->metode_pembayaran == 'tunai' ? 'Tunai' : ($order->metode_pembayaran == 'qris' ? 'QRIS' : 'Transfer Bank') }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Catatan</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0">{{ $order->catatan ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Daftar Item -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0">Makanan yang Dipesan</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Nama Makanan</th>
                                                                <th class="text-center">Harga</th>
                                                                <th class="text-center">Jumlah</th>
                                                                <th class="text-end">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($order->detailPemesanan as $item)
                                                                <tr>
                                                                    <td>{{ $item->menu->nama_menu }}</td>
                                                                    <td class="text-center">Rp
                                                                        {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="text-center">{{ $item->jumlah }}</td>
                                                                    <td class="text-end">Rp
                                                                        {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4" class="text-center">Tidak ada item</td>
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
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('reports.receipt', ['pesanan' => $order->id]) }}" class="btn btn-primary"
                                target="_blank">
                                <i class="fas fa-receipt me-1"></i> Cetak Struk
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endsection

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            $(document).ready(function() {
                // Initialize date range picker
                $('.date-range-picker').daterangepicker({
                    startDate: moment('{{ $startDate->format('Y-m-d') }}'),
                    endDate: moment('{{ $endDate->format('Y-m-d') }}'),
                    locale: {
                        format: 'DD/MM/YYYY'
                    }
                }, function(start, end) {
                    $('#start_date').val(start.format('YYYY-MM-DD'));
                    $('#end_date').val(end.format('YYYY-MM-DD'));
                });
            });
        </script>
    @endsection
