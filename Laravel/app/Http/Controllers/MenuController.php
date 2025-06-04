<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // Tambahkan baris ini

class MenuController extends Controller
{
    public function index()
    {
        // Path ke file status aplikasi
        $appStatusFilePath = storage_path('app/app_status.json');
        $appStatus = ['status' => 'open', 'message' => '']; // Default open

        // Baca status aplikasi dari file jika ada
        if (File::exists($appStatusFilePath)) {
            try {
                $fileContent = File::get($appStatusFilePath);
                $decodedContent = json_decode($fileContent, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                    $appStatus = array_merge($appStatus, $decodedContent);
                }
            } catch (\Exception $e) {
                // Log error jika gagal membaca/decode file
                Log::error('Failed to read app_status.json: ' . $e->getMessage());
            }
        } else {
            // Jika file tidak ada, buat dengan status default 'open'
            File::put($appStatusFilePath, json_encode($appStatus));
        }

        // Jika status aplikasi adalah 'closed', kembalikan respons khusus
        if ($appStatus['status'] === 'closed') {
            return response()->json([
                'app_status' => 'closed',
                'message' => $appStatus['message'] ?: 'Aplikasi sedang dalam pemeliharaan. Mohon coba lagi nanti.'
            ]);
        }

        // Jika status aplikasi 'open', kembalikan data menu normal
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
