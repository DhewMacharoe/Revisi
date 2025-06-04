<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use App\Models\Pelanggan;
use Barryvdh\DomPDF\Facade\Pdf;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::with(['pelanggan']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('metode_pembayaran') && $request->metode_pembayaran != '') {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $pesanan = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pesanan.index', compact('pesanan'));
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.nama_menu' => 'required|string',
                'items.*.jumlah' => 'required|integer',
                'items.*.harga' => 'required|integer',
                'total_harga' => 'required|integer',
                'metode_pembayaran' => 'required|string|in:tunai,qris,transfer bank,midtrans',
            ]);

            $pemesanan = new Pemesanan();
            $pemesanan->id_pelanggan = auth()->id;
            $pemesanan->total_harga = $request->total_harga;
            $pemesanan->status = 'menunggu';
            $pemesanan->metode_pembayaran = $request->metode_pembayaran;
            $pemesanan->waktu_pemesanan = now();
            $pemesanan->save();

            foreach ($request->items as $item) {
                DetailPemesanan::create([
                    'id_pemesanan' => $pemesanan->id,
                    'id_menu' => $item['id_menu'] ?? 1,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['jumlah'] * $item['harga'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'id' => $pemesanan->id,
                    'status' => $pemesanan->status,
                    'metode_pembayaran' => $pemesanan->metode_pembayaran,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $pesanan = Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])->findOrFail($id);
        return response()->json($pesanan);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }

    public function cetakStruk($id)
    {
        $pesanan = Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])->findOrFail($id);

        if (!in_array($pesanan->status, ['dibayar', 'diproses', 'selesai'])) {
            return redirect()->back()->with('error', 'Struk hanya dapat dicetak untuk pesanan yang sudah dibayar.');
        }

        $pdf = PDF::loadView('pesanan.struk', compact('pesanan'));
        return $pdf->stream('struk-pesanan-' . $id . '.pdf');
    }

    public function batalkanPesanan($id)
    {
        $pesanan = Pemesanan::findOrFail($id);

        if (in_array($pesanan->status, ['selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Pesanan yang sudah selesai atau dibatalkan tidak dapat dibatalkan.');
        }

        $pesanan->status = 'dibatalkan';
        $pesanan->save();

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
    public function ubahStatus($id, $status)
    {
        $pesanan = Pemesanan::findOrFail($id);
        $pesanan->status = strtolower($status);
        $pesanan->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        session(['cafeStatus' => $request->status]);
        $pesanan = Pemesanan::findOrFail($id);
        $pesanan->status = $request->status;
        $pesanan->save();

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }
}
