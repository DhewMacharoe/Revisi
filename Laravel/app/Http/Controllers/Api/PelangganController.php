<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('nama') && $request->has('telepon')) {
            $pelanggan = Pelanggan::where('nama', $request->nama)
                ->where('telepon', $request->telepon)
                ->first();

            if ($pelanggan) {
                return response()->json([$pelanggan], 200);
            } else {
                return response()->json([], 200);
            }
        }
        return response()->json(Pelanggan::all(), 200);
    }

    public function show($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        return response()->json($pelanggan, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:pelanggan,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $newPelanggan = Pelanggan::create([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'email' => $request->email,
            ]);

            DB::commit();
            return response()->json($newPelanggan, 201);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan pelanggan: ' . $e->getMessage());
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'email')) {
                return response()->json(['message' => 'Email sudah terdaftar.'], 409);
            }
            return response()->json(['message' => 'Gagal membuat pelanggan.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error umum saat menyimpan pelanggan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'telepon' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|required|string|email|max:255|unique:pelanggan,email,' . $pelanggan->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('nama')) {
            $pelanggan->nama = $request->input('nama');
        }
        if ($request->has('telepon')) {
            $pelanggan->telepon = $request->input('telepon');
        }
        if ($request->has('email')) {
            $pelanggan->email = $request->input('email');
        }

        $pelanggan->save();

        return response()->json($pelanggan, 200);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan'], 404);
        }

        $pelanggan->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus'], 200);
    }

    public function getByTelepon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telepon' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pelanggan = Pelanggan::where('telepon', $request->telepon)->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan dengan nomor telepon ini'], 404);
        }

        return response()->json($pelanggan, 200);
    }

    public function getByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;

        $pelanggan = Pelanggan::where('email', $email)->first();

        if (!$pelanggan) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan dengan email ini'], 404);
        }

        return response()->json($pelanggan, 200);
    }
}
