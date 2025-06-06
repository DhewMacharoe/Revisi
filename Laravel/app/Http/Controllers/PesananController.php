<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan; // Pastikan ini di-import jika digunakan secara langsung
use App\Models\Pelanggan;     // Pastikan ini di-import jika digunakan secara langsung
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log; // Tambahkan untuk logging error
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // Tambahkan untuk exception validasi

class PesananController extends Controller
{
    public function index(Request $request)
    {
        // Menggunakan nama relasi yang benar: detailPemesanan (camelCase)
        $query = Pemesanan::with(['pelanggan', 'detailPemesanan.menu']);

        $activeTab = $request->input('active_tab', 'diproses');
        
        if (in_array($activeTab, ['diproses', 'selesai', 'dibatalkan'])) {
            $query->where('status', $activeTab);
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        if ($request->filled('menu_search')) {
            $menuSearch = $request->menu_search;
            // Menggunakan nama relasi yang benar: detailPemesanan (camelCase)
            $query->whereHas('detailPemesanan.menu', function ($q) use ($menuSearch) {
                $q->where('nama_menu', 'like', '%' . $menuSearch . '%');
            });
        }

        $pesanan = $query->orderBy('created_at', 'desc')
                         ->paginate(10)
                         ->appends($request->except('page'));

        return view('pesanan.index', compact('pesanan', 'activeTab'));
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id_menu' => 'required|exists:menu,id',
                'items.*.nama_menu' => 'required|string', // Meskipun id_menu ada, nama_menu bisa tetap untuk referensi cepat
                'items.*.jumlah' => 'required|integer|min:1',
                'items.*.harga' => 'required|integer|min:0',
                // 'items.*.catatan' => 'nullable|string|max:255', // Jika ada
                // 'items.*.suhu' => 'nullable|string|max:50',    // Jika ada
                'total_harga' => 'required|integer|min:0',
                'metode_pembayaran' => 'required|string|in:tunai,qris,transfer bank,midtrans',
                'id_pelanggan' => 'required|exists:pelanggan,id', // Sebaiknya id_pelanggan dikirim dari frontend
            ]);

            $pemesanan = new Pemesanan();
            $pemesanan->id_pelanggan = $request->id_pelanggan; // Ambil dari request
            $pemesanan->admin_id = auth()->guard('admin')->id(); // Jika pesanan dibuat oleh admin yang login
            $pemesanan->total_harga = $request->total_harga;
            $pemesanan->status = 'menunggu';
            $pemesanan->metode_pembayaran = $request->metode_pembayaran;
            $pemesanan->waktu_pemesanan = now();
            // bukti_pembayaran dan waktu_pengambilan mungkin diisi nanti
            $pemesanan->save();

            foreach ($request->items as $item) {
                DetailPemesanan::create([
                    'id_pemesanan' => $pemesanan->id,
                    'id_menu' => $item['id_menu'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['jumlah'] * $item['harga'],
                    'catatan' => $item['catatan'] ?? null,
                    'suhu' => $item['suhu'] ?? null,
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error membuat pesanan: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: Terjadi kesalahan internal server.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Jika Anda memiliki form HTML standar untuk membuat pesanan (bukan hanya API dari method create),
        // implementasikan logikanya di sini. Jika tidak, bisa dibiarkan.
        // Contoh:
        // $validated = $request->validate([...]);
        // ... logika penyimpanan ...
        // alert()->success('Berhasil', 'Pesanan baru telah ditambahkan.');
        // return redirect()->route('pesanan.index');
        return redirect()->route('pesanan.index')->with('info', 'Fitur penambahan pesanan via form belum diimplementasikan di sini.');
    }

    public function show($id)
    {
        // Menggunakan nama relasi yang benar: detailPemesanan (camelCase)
        $pesanan = Pemesanan::with(['pelanggan', 'detailPemesanan.menu', 'admin'])->findOrFail($id);
        return response()->json($pesanan);
    }

    public function edit(string $id)
    {
        // $pesanan = Pemesanan::findOrFail($id);
        // return view('pesanan.edit', compact('pesanan'));
        return redirect()->route('pesanan.index')->with('info', 'Fitur edit pesanan belum diimplementasikan.');
    }

    public function update(Request $request, string $id)
    {
        // Logika untuk update pesanan
        return redirect()->route('pesanan.index')->with('info', 'Fitur update pesanan belum diimplementasikan.');
    }

    public function destroy(Request $request, string $id) // Tambahkan Request $request
    {
        $pesanan = Pemesanan::findOrFail($id);
        // Anda mungkin ingin menghapus detail pemesanan terkait juga jika tidak di-cascade oleh database
        // DetailPemesanan::where('id_pemesanan', $id)->delete();
        $deleted = $pesanan->delete();

        if ($deleted) {
            alert()->success('Berhasil', 'Pesanan #' . $id . ' berhasil dihapus.');
        } else {
            alert()->error('Gagal', 'Gagal menghapus pesanan #' . $id . '.');
        }
        // Mengambil active_tab dari request atau default ke 'diproses'
        return redirect()->route('pesanan.index', ['active_tab' => $request->input('active_tab', 'diproses')]);
    }

    public function cetakStruk($id)
    {
        // Menggunakan nama relasi yang benar: detailPemesanan (camelCase)
        $pesanan = Pemesanan::with(['pelanggan', 'detailPemesanan.menu', 'admin'])->findOrFail($id);

        if (!in_array($pesanan->status, ['dibayar', 'diproses', 'selesai'])) {
            alert()->warning('Gagal Cetak', 'Struk hanya dapat dicetak untuk pesanan yang sudah dibayar, diproses, atau selesai.');
            return redirect()->back();
        }

        $pdf = Pdf::loadView('pesanan.struk', compact('pesanan'));
        // Menggunakan Str::slug untuk nama file yang lebih aman
        return $pdf->stream(Str::slug('struk-pesanan-' . $pesanan->id . '-' . now()->format('YmdHis')) . '.pdf');
    }

    public function ubahStatus(Request $request, $id, $status)
    {
        $pesanan = Pemesanan::findOrFail($id);
        $statusValid = ['diproses', 'selesai', 'dibatalkan'];
        $newStatus = strtolower($status);

        if (!in_array($newStatus, $statusValid)) {
            alert()->error('Gagal', 'Status yang diminta tidak valid.');
            return redirect()->back();
        }
        
        if (($pesanan->status === 'selesai' && $newStatus === 'dibatalkan') || 
            ($pesanan->status === 'dibatalkan' && $newStatus !== 'dibatalkan')) {
            alert()->error('Gagal', 'Aksi status tidak diizinkan untuk status pesanan saat ini.');
            return redirect()->back();
        }

        $pesanan->status = $newStatus;
        $pesanan->save();

        alert()->success('Berhasil', 'Status pesanan #' . $id . ' berhasil diperbarui menjadi ' . ucfirst($newStatus) . '.');
        
        $redirectParams = $request->query(); // Ambil semua query string yang ada
        // Set active_tab ke status baru jika status baru adalah salah satu dari tab yang valid
        if(in_array($newStatus, ['diproses', 'selesai', 'dibatalkan'])){
            $redirectParams['active_tab'] = $newStatus;
        } else {
            // Jika status baru bukan salah satu tab utama (misal: 'dibayar'),
            // mungkin redirect ke tab 'diproses' atau tab yang paling relevan.
            $redirectParams['active_tab'] = $redirectParams['active_tab'] ?? 'diproses';
        }
        
        return redirect()->route('pesanan.index', $redirectParams);
    }

    // Method updateStatus (jika digunakan untuk API/AJAX) bisa dipertimbangkan kembali.
    // Untuk saat ini, form di Blade Anda menggunakan route yang mengarah ke `ubahStatus`.
    /*
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string|in:menunggu,pembayaran,dibayar,diproses,selesai,dibatalkan']);
        $pesanan = Pemesanan::findOrFail($id);
        $pesanan->status = $request->status;
        $pesanan->save();
        return response()->json(['message' => 'Status berhasil diperbarui', 'new_status' => $pesanan->status]);
    }
    */
}
