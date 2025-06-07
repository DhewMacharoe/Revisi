<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    /**
     * Memastikan hanya admin yang terautentikasi yang bisa mengakses.
     * Middleware 'auth:admin' akan diterapkan melalui file route.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Menampilkan form untuk mengedit PIN.
     * Dapat diakses oleh semua admin yang login.
     */
    public function showPinForm()
    {
        // Mencari PIN di database, jika tidak ada, buat baru dengan nilai null.
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
        // Mengubah validasi menjadi 'digits:6' untuk memastikan panjangnya tepat 6
        // dan tetap mempertahankan 'numeric'.
        $request->validate([
            'pin' => 'required|numeric|digits:6|confirmed',
        ], [
            'pin.numeric' => 'PIN harus berupa angka.',
            'pin.digits' => 'PIN harus terdiri dari 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
        ]);

        Setting::updateOrCreate(
            ['key' => 'registration_pin'],
            [
                'value' => Hash::make($request->pin),
                'updated_by_admin_id' => Auth::guard('admin')->id()
            ]
        );

        return redirect()->route('pin.edit')->with('success_sweetalert', 'PIN Registrasi berhasil diperbarui!');
    }
}
