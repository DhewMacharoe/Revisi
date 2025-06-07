<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// use RealRashid\SweetAlert\Facades\Alert; // Uncomment jika ingin menggunakan fasad Alert::

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Menu::query();

        // Filter berdasarkan kategori
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_menu', 'like', '%' . $request->search . '%');
        }

        $produk = $query->orderBy('nama_menu')->paginate(10);

        return view('produk.index', compact('produk'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('produk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:50',
            'kategori' => 'required|in:makanan,minuman',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Tambahkan webp jika perlu
        ]);

        $data = $request->all();
        $data['id_admin'] = Auth::guard('admin')->id();
        $data['stok_terjual'] = 0; // Default stok terjual

        if ($request->hasFile('gambar')) {
            try {
                $gambar = $request->file('gambar');
                $nama_file = time() . '_' . preg_replace('/\s+/', '_', $gambar->getClientOriginalName());

                // Simpan ke public/storage/menu
                // Pastikan direktori 'storage/menu' dapat ditulis oleh server.
                $gambar->move(public_path('storage/menu'), $nama_file);
                $data['gambar'] = 'menu/' . $nama_file; // Path relatif untuk disimpan di DB
            } catch (\Exception $e) {
                // Jika gagal upload gambar
                alert()->error('Gagal Upload', 'Terjadi kesalahan saat mengupload gambar: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        $menu = Menu::create($data);

        if ($menu) {
            alert()->success('Berhasil', 'Produk berhasil ditambahkan.');
        } else {
            alert()->error('Gagal', 'Terjadi kesalahan saat menambahkan produk.');
            return redirect()->back()->withInput(); // Kembali dengan input jika gagal simpan
        }

        return redirect()->route('produk.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produk = Menu::findOrFail($id);
        return view('produk.show', compact('produk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produk = Menu::findOrFail($id);
        return view('produk.edit', compact('produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:50',
            'kategori' => 'required|in:makanan,minuman',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Tambahkan webp jika perlu
        ]);

        $produk = Menu::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('gambar')) {
            try {
                // Hapus gambar lama jika ada dan file-nya memang ada
                if ($produk->gambar && file_exists(public_path('storage/' . $produk->gambar))) {
                    unlink(public_path('storage/' . $produk->gambar));
                } elseif ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                    // Alternatif jika path disimpan tanpa 'menu/' prefix di DB dan menggunakan Storage facade
                    // Storage::disk('public')->delete($produk->gambar);
                }

                $gambar = $request->file('gambar');
                $nama_file = time() . '_' . preg_replace('/\s+/', '_', $gambar->getClientOriginalName());
                $gambar->move(public_path('storage/menu'), $nama_file);
                $data['gambar'] = 'menu/' . $nama_file;
            } catch (\Exception $e) {
                alert()->error('Gagal Upload', 'Terjadi kesalahan saat mengupload gambar baru: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        $updated = $produk->update($data);

        if ($updated) {
            alert()->success('Berhasil', 'Produk berhasil diperbarui.');
        } else {
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui produk.');
            return redirect()->back()->withInput();
        }

        return redirect()->route('produk.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produk = Menu::findOrFail($id);

        try {
            // Hapus gambar dari storage jika ada
            // Pastikan path yang digunakan untuk menyimpan dan menghapus konsisten
            if ($produk->gambar) {
                // Jika path di DB adalah 'menu/namafile.jpg'
                $pathGambarPublic = 'storage/' . $produk->gambar; // Path relatif dari public folder
                if (file_exists(public_path($pathGambarPublic))) {
                    unlink(public_path($pathGambarPublic));
                }
                // Atau jika Anda menyimpan path lengkap atau menggunakan Storage facade dengan disk 'public'
                // if (Storage::disk('public')->exists($produk->gambar)) {
                //     Storage::disk('public')->delete($produk->gambar);
                // }
            }

            $deleted = $produk->delete();

            if ($deleted) {
                alert()->success('Berhasil', 'Produk berhasil dihapus.');
            } else {
                // Ini jarang terjadi jika findOrFail berhasil dan tidak ada exception lain
                alert()->error('Gagal', 'Produk gagal dihapus.');
            }
        } catch (\Exception $e) {
            // Menangkap exception lain yang mungkin terjadi (misalnya, masalah foreign key constraint)
            alert()->error('Gagal', 'Terjadi kesalahan saat menghapus produk: ' . $e->getMessage());
        }

        return redirect()->route('produk.index');
    }
}
