<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Import DB Facade untuk transaksi
use Illuminate\Support\Facades\Log; // Import Log Facade untuk logging error

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
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            // Pastikan validasi email unik tidak bermasalah saat mencoba insert dengan ID spesifik
            // Untuk unique check, biasanya tidak masalah karena kita cek keberadaan ID dulu
            'email' => 'required|string|email|max:255|unique:pelanggan,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pelangganData = [
            'nama' => $request->nama,
            'telepon' => $request->telepon,
            'email' => $request->email,
        ];

        $newPelanggan = null;
        $targetId = 1; // ID spesifik yang ingin diisi jika kosong

        DB::beginTransaction(); // Memulai transaksi

        try {
            $pelangganWithTargetIdExists = Pelanggan::where('id', $targetId)->exists();

            if (!$pelangganWithTargetIdExists) {
                $pelangganInstanceById = new Pelanggan($pelangganData);
                $pelangganInstanceById->id = $targetId; // Set ID secara manual

                if ($pelangganInstanceById->save()) {
                    $newPelanggan = $pelangganInstanceById;
                } else {
                    Log::warning('Gagal menyimpan pelanggan dengan ID spesifik ' . $targetId . ' meskipun ID terdeteksi kosong.');
                }
            }
            if (!$newPelanggan) {
                $newPelanggan = Pelanggan::create($pelangganData);
            }

            DB::commit(); 
            return response()->json($newPelanggan, 201);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack(); // Batalkan transaksi jika ada error query (misal, email duplikat yang lolos validasi awal karena race condition)
            Log::error('Error saat menyimpan pelanggan: ' . $e->getMessage());
            // Cek apakah error karena ID target sudah ada (jika race condition terjadi antara check dan save)
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), "for key 'PRIMARY'") && !$pelangganWithTargetIdExists) {
                // Kemungkinan race condition, ID 1 terisi tepat sebelum save() ini. Coba buat secara normal.
                try {
                    DB::beginTransaction(); // Transaksi baru untuk percobaan kedua
                    $newPelangganFallback = Pelanggan::create($pelangganData);
                    DB::commit();
                    return response()->json($newPelangganFallback, 201);
                } catch (\Exception $exFallback) {
                    DB::rollBack();
                    return response()->json(['message' => 'Gagal membuat pelanggan setelah percobaan fallback.', 'error' => $exFallback->getMessage()], 500);
                }
            }
            return response()->json(['message' => 'Gagal membuat pelanggan.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika ada error lain
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

        // Gunakan $request->input() untuk keamanan dan konsistensi
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

        return response()->json($pelanggan);
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

        return response()->json($pelanggan);
    }
}
