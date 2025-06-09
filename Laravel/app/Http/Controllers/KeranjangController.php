<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Keranjang;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Carbon\Carbon;

class KeranjangController extends Controller
{
    public function index()
    {
        $keranjangs = Keranjang::with(['pelanggan', 'menu'])->get();
        return response()->json($keranjangs);
    }

    public function show($id)
    {
        $keranjang = Keranjang::with(['pelanggan', 'menu'])->findOrFail($id);
        return response()->json($keranjang);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Request received:', $request->all());

            $validated = $request->validate([
                'id_pelanggan' => 'required|exists:pelanggan,id',
                'id_menu' => 'required|exists:menu,id',
                'nama_menu' => 'required|string|max:255',
                'kategori' => 'required|in:makanan,minuman',
                'harga' => 'required|numeric|min:0',
                'jumlah' => 'required|integer|min:1',
                'suhu' => 'nullable|string|max:20',
                'catatan' => 'nullable|string|max:255',
            ]);

            Log::info('Validated data:', $validated);

            $existingItem = Keranjang::where('id_pelanggan', $validated['id_pelanggan'])
                ->where('id_menu', $validated['id_menu'])
                ->when($validated['kategori'] == 'minuman', function ($query) use ($validated) {
                    return $query->where('suhu', $validated['suhu']);
                })
                ->first();

            if ($existingItem) {
                $existingItem->increment('jumlah', $validated['jumlah']);
                Log::info('Item quantity updated:', $existingItem->toArray());
                return response()->json([
                    'message' => 'Jumlah item berhasil ditambah',
                    'data' => $existingItem
                ], 200)->header('Content-Type', 'application/json');
            }

            $keranjang = Keranjang::create($validated);
            Log::info('New item created:', $keranjang->toArray());
            return response()->json([
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'data' => $keranjang
            ], 201)->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function update(Request $request, $id)
    {
        $keranjang = Keranjang::findOrFail($id);
        $validated = $request->validate([
            'id_pelanggan' => 'sometimes|exists:pelanggan,id',
            'id_menu' => 'sometimes|exists:menu,id',
            'nama_menu' => 'sometimes|string|max:50',
            'kategori' => 'sometimes|in:makanan,minuman',
            'harga' => 'sometimes|numeric|min:0',
            'jumlah' => 'sometimes|integer|min:1',
            'suhu' => 'nullable|string|max:20',
            'catatan' => 'nullable|string|max:255',
        ]);
        $keranjang->update($validated);
        return response()->json($keranjang);
    }

    public function destroy($id)
    {
        $keranjang = Keranjang::findOrFail($id);
        $keranjang->delete();
        return response()->json(['message' => 'Item berhasil dihapus dari keranjang']);
    }

    public function clearCart($id_pelanggan)
    {
        Keranjang::where('id_pelanggan', $id_pelanggan)->delete();
        return response()->json(['message' => 'Keranjang berhasil dikosongkan']);
    }

    public function getCartItemCount($id_pelanggan)
    {
        try {
            $count = Keranjang::where('id_pelanggan', $id_pelanggan)->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error getting cart item count: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mendapatkan jumlah item keranjang'], 500);
        }
    }

    public function getCartByCustomer($id_pelanggan)
    {
        $keranjangs = Keranjang::where('id_pelanggan', $id_pelanggan)
            ->with('menu')
            ->get();
        return response()->json($keranjangs);
    }

    public function checkout($id_pelanggan)
    {
        $keranjangs = Keranjang::where('id_pelanggan', $id_pelanggan)->get();
        if ($keranjangs->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }
        $totalHarga = $keranjangs->sum(function ($item) {
            return $item->harga * $item->jumlah;
        });
        $pemesanan = Pemesanan::create([
            'id_pelanggan' => $id_pelanggan,
            'total_harga' => $totalHarga,
            'status' => 'menunggu',
            'waktu_pemesanan' => Carbon::now(),
        ]);
        foreach ($keranjangs as $item) {
            DetailPemesanan::create([
                'id_pemesanan' => $pemesanan->id,
                'id_menu' => $item->id_menu,
                'jumlah' => $item->jumlah,
                'harga_satuan' => $item->harga,
                'subtotal' => $item->harga * $item->jumlah,
                'catatan' => $item->catatan,
                'suhu' => $item->suhu,
            ]);
        }
        Keranjang::where('id_pelanggan', $id_pelanggan)->delete();
        return response()->json([
            'message' => 'Pemesanan berhasil dibuat',
            'pemesanan' => $pemesanan
        ], 201);
    }

    public function getByPelanggan($id_pelanggan)
    {
        $keranjang = Keranjang::where('id_pelanggan', $id_pelanggan)
            ->with('menu')
            ->get();
        return response()->json($keranjang);
    }
}
