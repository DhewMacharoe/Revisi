<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DetailPemesanan;
use App\Notifications\PesananMasukNotification;

class PemesananController extends Controller
{
    // Tampilkan semua pesanan dengan detailnya
    public function index()
    {
        // [DIUBAH] Menambahkan relasi pelanggan untuk konsistensi
        return response()->json(Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])->get());
    }

    // Simpan pesanan baru beserta detailnya dan kirim notifikasi ke admin
    public function store(Request $request)
    {
        Log::info('Incoming request:', $request->all());

        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'admin_id' => 'nullable|integer',
            'total_harga' => 'required|integer',
            'metode_pembayaran' => 'required|in:tunai,qris,transfer bank',
            'waktu_pemesanan' => 'nullable|date',
            'waktu_pengambilan' => 'nullable|date',
            'status' => 'nullable|in:menunggu,pembayaran,dibayar,diproses,selesai,dibatalkan',
            'detail_pemesanan' => 'required|array|min:1',
            'detail_pemesanan.*.id_menu' => 'required|exists:menu,id',
            'detail_pemesanan.*.jumlah' => 'required|integer|min:1',
            'detail_pemesanan.*.harga_satuan' => 'required|integer|min:0',
            'detail_pemesanan.*.subtotal' => 'required|integer|min:0',
            'detail_pemesanan.*.suhu' => 'nullable|string|max:20',
            'detail_pemesanan.*.catatan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $pemesanan = Pemesanan::create([
                'id_pelanggan' => $request->id_pelanggan,
                'admin_id' => $request->admin_id,
                'total_harga' => $request->total_harga,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $request->status ?? 'menunggu',
                'waktu_pemesanan' => now(),
                'waktu_pengambilan' => $request->waktu_pengambilan,
            ]);

            foreach ($request->detail_pemesanan as $item) {
                $pemesanan->detailPemesanan()->create([
                    'id_menu' => $item['id_menu'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal'],
                    'catatan' => $item['catatan'] ?? null,
                    'suhu' => $item['suhu'] ?? null,
                ]);
            }

            DB::commit();
            Log::info('DETAIL PEMESANAN MASUKAN:', $request->detail_pemesanan);
            // Kirim notifikasi ke semua admin
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new PesananMasukNotification($pemesanan));
            }

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan dan detail berhasil disimpan',
                // [DIUBAH] Menambahkan relasi pelanggan di sini juga
                'data' => $pemesanan->load(['pelanggan', 'detailPemesanan'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pemesanan gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tampilkan pesanan berdasarkan id
    public function show(string $id)
    {
        // [DIUBAH] Menambahkan relasi 'pelanggan' dan 'detailPemesanan.menu'
        $pemesanan = Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])->find($id);
        if (!$pemesanan) {
            return response()->json(['message' => 'Pemesanan tidak ditemukan'], 404);
        }
        return response()->json($pemesanan);
    }

    // Update pesanan (partial update)
    public function update(Request $request, string $id)
    {
        $pemesanan = Pemesanan::find($id);
        if (!$pemesanan) {
            return response()->json(['message' => 'Pemesanan tidak ditemukan'], 404);
        }

        $request->validate([
            'total_harga' => 'sometimes|integer',
            'metode_pembayaran' => 'sometimes|in:tunai,qris,transfer bank',
            'status' => 'sometimes|in:menunggu,pembayaran,dibayar,diproses,selesai,dibatalkan',
            'waktu_pemesanan' => 'nullable|date',
            'waktu_pengambilan' => 'nullable|date',
            'catatan_pembatalan' => 'nullable|string|max:255|required_if:status,dibatalkan',
        ]);
        Log::info('Data received in PemesananController update:', $request->all());

        $pemesanan->update($request->only([
            'total_harga',
            'metode_pembayaran',
            'status',
            'waktu_pemesanan',
            'waktu_pengambilan',
            'catatan_pembatalan'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil diupdate',
            'data' => $pemesanan
        ]);
    }

    // Hapus pesanan
    public function destroy(string $id)
    {
        $pemesanan = Pemesanan::find($id);
        if (!$pemesanan) {
            return response()->json(['message' => 'Pemesanan tidak ditemukan'], 404);
        }
        $pemesanan->delete();

        return response()->json(['message' => 'Pemesanan berhasil dihapus']);
    }

    // Ambil pesanan berdasarkan pelanggan, bisa filter status
    public function getByPelanggan($id, Request $request)
    {
        $status = $request->query('status');

        // [DIUBAH] Menambahkan 'pelanggan' ke dalam eager loading
        $query = Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])
            ->where('id_pelanggan', $id);

        if ($status) {
            // Menangani jika status dikirim sebagai array (contoh: 'menunggu,pembayaran')
            if(is_string($status) && str_contains($status, ',')) {
                $statuses = explode(',', $status);
                $query->whereIn('status', $statuses);
            } else {
                $query->where('status', $status);
            }
        }

        $pesanan = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($pesanan);
    }

    // Simpan rating di detail pemesanan
    public function beriRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $detail = DetailPemesanan::findOrFail($id);
        $detail->rating = $request->rating;
        $detail->save();

        return response()->json(['message' => 'Rating disimpan']);
    }

    // Cek pesanan baru sejak waktu terakhir client polling (untuk notifikasi polling tanpa websocket)
    public function cekPesananBaru(Request $request)
    {
        $lastChecked = $request->query('last_checked');

        if (!$lastChecked) {
            // Kalau client tidak kirim waktu terakhir, cek pesanan 5 menit terakhir
            $lastChecked = now()->subMinutes(5)->toDateTimeString();
        }

        $newOrdersCount = Pemesanan::where('created_at', '>', $lastChecked)->count();

        return response()->json([
            'new_orders' => $newOrdersCount,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function getFinishedOrderIds($pelangganId)
    {
        $finishedOrderIds = Pemesanan::where('id_pelanggan', $pelangganId)
            ->where('status', 'selesai')
            ->orderBy('created_at', 'desc') // Urutkan agar konsisten
            ->pluck('id'); // Ambil hanya kolom 'id'

        return response()->json(['order_ids' => $finishedOrderIds]);
    }
}

