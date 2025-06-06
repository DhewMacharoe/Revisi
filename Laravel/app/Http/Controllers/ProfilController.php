<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password; // Untuk aturan password yang lebih kuat

class ProfilController extends Controller
{
    /**
     * Menampilkan halaman profil (hanya tampilan).
     * Anda perlu membuat view 'profil.index' jika belum ada.
     */
    public function index()
    {
        // Mengambil data admin/user yang sedang login
        // Ganti 'admin' dengan guard yang sesuai jika Anda menggunakan guard kustom
        $admin = Auth::user(); 
        return view('profil.index', compact('admin'));
    }

    /**
     * Menampilkan form untuk mengedit profil.
     */
    public function edit()
    {
        $admin = Auth::user(); // Ganti 'admin' dengan guard yang sesuai
        return view('profil.edit', compact('admin')); // Mengarah ke profil/edit.blade.php
    }

    /**
     * Memproses pembaruan profil.
     */
    public function update(Request $request)
    {
        // Ganti 'admin' dengan guard yang sesuai jika Anda menggunakan guard kustom
        $admin = Auth::user(); 

        // Asumsi nama tabel untuk admin adalah 'users' atau 'admins'. Sesuaikan 'users' di bawah ini.
        // Jika model Admin Anda secara eksplisit mendefinisikan $table, gunakan itu.
        // Jika tidak, Laravel akan mengasumsikan bentuk jamak dari nama model (misal, User -> users, Admin -> admins).
        $adminTableName = $admin->getTable(); // Cara aman mendapatkan nama tabel model

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique($adminTableName, 'email')->ignore($admin->id),
            ],
            'current_password' => [ // Hanya wajib jika password baru diisi
                'nullable',
                'required_with:password', 
                function ($attribute, $value, $fail) use ($admin) {
                    if (!empty($value) && !Hash::check($value, $admin->password)) {
                        $fail('Password lama tidak sesuai.');
                    }
                },
            ],
            'password' => [ // Password baru
                'nullable',
                'required_with:current_password', 
                'confirmed', // Harus ada field 'password_confirmation' yang cocok
                // Anda bisa menggunakan aturan password yang lebih kompleks dari Laravel:
                // Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
                // Atau aturan sederhana:
                'min:6', 
            ],
        ], [
            'current_password.required_with' => 'Password lama wajib diisi untuk mengubah password.',
            'password.required_with' => 'Password baru wajib diisi jika password lama dimasukkan.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal harus 6 karakter.',
        ]);

        $admin->nama = $request->nama;
        $admin->email = $request->email;

        // Update password hanya jika field password baru diisi dan valid
        if (!empty($request->password)) {
            // Pengecekan current_password sudah dilakukan oleh validasi 'required_with' dan custom rule.
            // Jika current_password kosong tapi password baru diisi, validasi 'required_with' akan gagal.
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        // Redirect kembali ke halaman edit dengan pesan sukses untuk SweetAlert
        return redirect()->route('profil.edit')->with('success_sweetalert', 'Profil berhasil diperbarui!');
    }
}
