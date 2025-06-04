<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::query();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian berdasarkan nama atau telepon
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('telepon', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSorts = ['nama', 'telepon', 'status'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'nama';
        }

        $pelanggan = $query->orderBy($sortBy, $sortOrder)
            ->paginate(10)
            ->appends($request->all());

        return view('pelanggan.index', compact('pelanggan', 'sortBy', 'sortOrder'));
    }
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        foreach ($pelanggan->pemesanan as $pemesanan) {
            $pemesanan->detailPemesanan()->delete();
            $pemesanan->pembayaran()->delete();
            $pemesanan->delete();
        }

        $pelanggan->keranjang()->delete();
        $pelanggan->ratings()->delete();

        if ($pelanggan->pemesanan()->exists()) {
            return redirect()->route('pelanggan.index')
                ->with('error', 'Tidak dapat menghapus pelanggan karena masih memiliki data pemesanan.');
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
