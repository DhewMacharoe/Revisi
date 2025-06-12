<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Struk Pesanan #{{ $pesanan->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            padding: 0;
        }

        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            padding: 0;
        }

        .header p {
            margin: 2px 0;
            padding: 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 2px 0;
        }

        .right {
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DELBITES</h1>
        <p>Jl. Sisingamangaraja
Desa Sitoluama-Kec. Laguboti
Kab. Tobasa, Sumatera Utara,
Indonesia
</p>
        <p>Telp : (+62) 813 6091 2900</p>
        <p>================================</p>
        <h2>STRUK PESANAN</h2>
        <p>No: #ORD-{{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}</p>
        <p>Tanggal: {{ $pesanan->created_at->format('d/m/Y H:i') }}</p>
        <p>Kasir: Admin</p>
        <p>================================</p>
    </div>

    <table>
        <tr>
            <td colspan="2">Pelanggan: {{ $pesanan->pelanggan->nama }}</td>
        </tr>
        <tr>
            <td colspan="2">Pembayaran:
                @if ($pesanan->metode_pembayaran == 'tunai')
                    Tunai
                @elseif($pesanan->metode_pembayaran == 'qris')
                    QRIS
                @elseif($pesanan->metode_pembayaran == 'transfer bank')
                    Transfer Bank
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <th>Item</th>
            <th class="right">Qty</th>
            <th class="right">Harga</th>
            <th class="right">Subtotal</th>
        </tr>
        @foreach ($pesanan->detailPemesanan as $item)
            <tr>
                <td>{{ $item->menu->nama_menu }}</td>
                <td class="right">{{ $item->jumlah }}</td>
                <td class="right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <th>TOTAL</th>
            <th class="right">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>&copy; Delbites 2025</p>
    </div>
</body>

</html>
