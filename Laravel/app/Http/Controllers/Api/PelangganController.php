<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index()
    {
        return response()->json(Pelanggan::all());
    }

    public function show($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        return response()->json($pelanggan);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'telepon' => 'nullable|string',
            'device_id' => 'required|string|unique:pelanggan,device_id',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => $request->nama,
            'telepon' => $request->telepon,
            'device_id' => strtolower($request->device_id),
        ]);

        return response()->json($pelanggan, 201);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        $pelanggan->update([
            'nama' => $request->nama ?? $pelanggan->nama,
            'telepon' => $request->telepon ?? $pelanggan->telepon,
        ]);

        return response()->json($pelanggan);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        $pelanggan->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus']);
    }

    public function getByTelepon(Request $request)
    {
        $request->validate(['telepon' => 'required|string']);

        $pelanggan = Pelanggan::where('telepon', $request->telepon)->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json($pelanggan);
    }

    public function getByDevice(Request $request)
    {
        $request->validate(['device_id' => 'required|string']);
    
        $deviceId = strtolower($request->device_id); // ✅ konversi ke lowercase
    
        $pelanggan = Pelanggan::whereRaw('LOWER(device_id) = ?', [$deviceId])->first();
    
        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }
    
        return response()->json($pelanggan);
    }
    
    
}
