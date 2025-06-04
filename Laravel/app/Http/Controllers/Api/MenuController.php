<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use App\Models\DetailPemesanan;
use App\Models\Rating;


class MenuController extends Controller
{
    // Menampilkan semua menu dengan total penjualan
    public function index()
    {
        $menu = Menu::with('detailPemesanans')->get();

        // Menambahkan total penjualan per menu
        $menu = $menu->map(function ($item) {
            $item->total_terjual = $item->detailPemesanans->sum('jumlah');
            return $item;
        });

        return response()->json($menu);
    }

    // Menampilkan menu tertentu berdasarkan ID
    public function show($id)
    {
        return response()->json(Menu::findOrFail($id));
    }

    // Menambahkan menu baru
    public function store(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,admin_id',
            'nama_menu' => 'required|string|max:50',
            'kategori' => 'required|in:makanan,minuman',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|string|max:100',
        ]);

        $menu = Menu::create($request->all());
        return response()->json($menu, 201);
    }

    // Memperbarui menu
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $request->validate([
            'admin_id' => 'sometimes|exists:admins,admin_id',
            'nama_menu' => 'sometimes|string|max:50',
            'kategori' => 'sometimes|in:makanan,minuman',
            'harga' => 'sometimes|numeric|min:0',
            'stok' => 'sometimes|integer|min:0',
            'gambar' => 'nullable|string|max:100',
        ]);

        $menu->update($request->all());
        return response()->json($menu);
    }

    // Menghapus menu
    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

    // Menampilkan menu terlaris
    public function topMenu()
    {
        $menu = Menu::with('detailPemesanans')->get();

        // Menambahkan total penjualan per menu
        $menu = $menu->map(function ($item) {
            $item->total_terjual = $item->detailPemesanans->sum('jumlah');
            return $item;
        });

        // Urutkan berdasarkan jumlah penjualan terbanyak
        $sortedMenu = $menu->sortByDesc('total_terjual')->values()->take(8);

        return response()->json($sortedMenu);
    }
    public function getTopMenu()
    {
        $topMenu = Menu::getTopMenuItems();

        return response()->json([
            'success' => true,
            'message' => 'Top 8 menu terlaris berhasil diambil',
            'data' => $topMenu
        ]);
    }

    // public function beriRating(Request $request)
    // {
    //     $request->validate([
    //         'id_menu' => 'required|exists:menu,id',
    //         'id_pelanggan' => 'required|exists:pelanggan,id',
    //         'rating' => 'required|integer|min:1|max:5',
    //     ]);
    
    //     // Simpan atau update rating
    //     Rating::updateOrCreate(
    //         ['id_menu' => $request->id_menu, 'id_pelanggan' => $request->id_pelanggan],
    //         ['rating' => $request->rating]
    //     );
    
    //     // Hitung ulang rata-rata
    //     $average = Rating::where('id_menu', $request->id_menu)->avg('rating');
    
    //     $menu = Menu::find($request->id_menu);
    //     $menu->rating = $average;
    //     $menu->save();
    
    //     return response()->json(['message' => 'Rating berhasil disimpan', 'average' => $average]);
    // }

    // public function storeRating(Request $request)
    // {
    //     $validated = $request->validate([
    //         'id_menu' => 'required|exists:menu,id',
    //         'rating' => 'required|numeric|min:1|max:5',
    //     ]);

    //     DB::table('ratings')->insert([
    //         'id_menu' => $validated['id_menu'],
    //         'rating' => $validated['rating'],
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ]);

    //     return response()->json(['message' => 'Rating disimpan']);
    // }

    public function updateRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5'
        ]);
    
        $detail = DetailPemesanan::findOrFail($id);
        $detail->rating = $request->rating;
        $detail->save();
    
        // Update akumulasi rating menu
        $this->updateMenuRating($detail->id_menu);
    
        return response()->json(['message' => 'Rating berhasil disimpan']);
    }
    
}
