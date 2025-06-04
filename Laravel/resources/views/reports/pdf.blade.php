<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan - DelBites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENDAPATAN DELBITES</h1>
        <p>Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <span class="summary-label">Total Pendapatan:</span> Rp {{ number_format($totalIncome, 0, ',', '.') }}
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Pesanan:</span> {{ $totalOrders }}
        </div>
    </div>
    
    <h3>Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>#ORD-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $order->pelanggan->nama ?? '-' }}</td>
                <td>
                    @if($order->status == 'menunggu')
                        Menunggu
                    @elseif($order->status == 'pembayaran')
                        Pembayaran
                    @elseif($order->status == 'dibayar')
                        Dibayar
                    @elseif($order->status == 'diproses')
                        Diproses
                    @elseif($order->status == 'selesai')
                        Selesai
                    @elseif($order->status == 'dibatalkan')
                        Dibatalkan
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>&copy; DelBites 2025 - Laporan ini dicetak secara otomatis</p>
    </div>
</body>
</html>