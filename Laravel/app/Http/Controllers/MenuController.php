<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        return response()->json(Menu::all());
    }
    public function show($id)
    {
        return response()->json(Menu::findOrFail($id));
    }

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

    public function destroy($id)
    {
        Menu::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu berhasil dihapus']);
    }
}
