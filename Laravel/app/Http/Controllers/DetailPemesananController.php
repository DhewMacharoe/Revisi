<?php

namespace App\Http\Controllers;

use App\Models\DetailPemesanan;
use Illuminate\Http\Request;
use App\Models\Menu;

class DetailPemesananController extends Controller
{
    public function index()
    {
        return DetailPemesanan::with(['pemesanan', 'menu'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required|exists:pemesanan,id',
            'id_menu' => 'required|exists:menu,id',
            'jumlah' => 'required|integer',
            'harga_satuan' => 'required|integer',
            'subtotal' => 'required|integer',
        ]);

        return DetailPemesanan::create($request->all());
    }

    public function show($id)
    {
        return DetailPemesanan::with(['pemesanan', 'menu'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $detail = DetailPemesanan::findOrFail($id);
        $detail->update($request->all());
        return $detail;
    }

    public function destroy($id)
    {
        DetailPemesanan::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function updateRating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $detail = DetailPemesanan::findOrFail($id);
        $detail->rating = $request->rating;
        $detail->save();

        // Update rating rata-rata ke tabel menu
        $this->updateMenuRating($detail->id_menu);

        return response()->json(['message' => 'Rating berhasil disimpan dan diperbarui']);
    }

    protected function updateMenuRating($menuId)
    {
        $avgRating = DetailPemesanan::where('id_menu', $menuId)
            ->whereNotNull('rating')
            ->avg('rating');

        Menu::where('id', $menuId)->update([
            'rating' => round($avgRating ?? 0, 2)
        ]);
    }
}
