<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    // Hanya admin dengan ID 1 yang bisa mengakses semua method di controller ini
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::guard('admin')->id() !== 1) {
                abort(403, 'AKSES DITOLAK. HANYA SUPER ADMIN YANG DIIZINKAN.');
            }
            return $next($request);
        });
    }

    // Menampilkan form untuk mengedit PIN
    public function showPinForm()
    {
        // Cari PIN di database, jika tidak ada, buat baru dengan nilai null
        $pinSetting = Setting::firstOrCreate(
            ['key' => 'registration_pin'],
            ['value' => null, 'updated_by_admin_id' => Auth::id()]
        );

        return view('pengaturan.pin', compact('pinSetting'));
    }

    // Memproses update PIN
    public function updatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:6|confirmed', // 'pin_confirmation' harus ada di form
        ]);

        // Update atau buat data PIN
        Setting::updateOrCreate(
            ['key' => 'registration_pin'],
            [
                // Simpan PIN sebagai hash untuk keamanan
                'value' => Hash::make($request->pin),
                'updated_by_admin_id' => Auth::id()
            ]
        );

        return redirect()->route('pin.edit')->with('success_sweetalert', 'PIN Registrasi berhasil diperbarui!');
    }
}
