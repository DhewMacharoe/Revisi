<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    /**
     * Konstruktor tidak lagi membatasi akses hanya untuk ID 1.
     * Semua admin yang terautentikasi (melalui middleware di route) bisa mengakses method ini.
     */
    public function __construct()
    {
        // Pastikan middleware 'auth:admin' diterapkan di file route Anda untuk controller ini.
        $this->middleware('auth:admin');
    }

    /**
     * Menampilkan form untuk mengedit PIN.
     * Dapat diakses oleh semua admin yang login.
     */
    public function showPinForm()
    {
        // Mencari PIN di database, jika tidak ada, buat baru dengan nilai null.
        // 'updated_by_admin_id' akan diisi dengan ID admin yang pertama kali membuka halaman ini jika PIN belum ada.
        $pinSetting = Setting::firstOrCreate(
            ['key' => 'registration_pin'],
            ['value' => null, 'updated_by_admin_id' => Auth::guard('admin')->id()]
        );

        return view('pengaturan.pin', compact('pinSetting'));
    }

    /**
     * Memproses update PIN.
     * Dapat dijalankan oleh semua admin yang login.
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:6|confirmed', // 'pin_confirmation' harus ada di form
        ]);

        // Update atau buat data PIN.
        // updated_by_admin_id akan diisi dengan ID admin yang melakukan perubahan.
        Setting::updateOrCreate(
            ['key' => 'registration_pin'],
            [
                // Simpan PIN sebagai hash untuk keamanan
                'value' => Hash::make($request->pin),
                'updated_by_admin_id' => Auth::guard('admin')->id()
            ]
        );

        return redirect()->route('pin.edit')->with('success_sweetalert', 'PIN Registrasi berhasil diperbarui!');
    }
}
