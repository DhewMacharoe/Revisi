<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalOperasional;

class JadwalOperasionalController extends Controller
{
    public function index()
    {
        $jadwal = JadwalOperasional::all();
        // Assuming you have moved the file to 'resources/views/jadwal/index.blade.php'
        return view('jadwal.index', compact('jadwal'));
    }

    public function update(Request $request)
    {
        // Get the submitted 'jadwal' data. Default to an empty array if not present.
        // This immediately fixes the "null given" error.
        $submittedData = $request->input('jadwal', []);

        // Get all schedules from the database to ensure every day is processed.
        $allSchedules = JadwalOperasional::all();

        foreach ($allSchedules as $schedule) {
            // Check if the current schedule's ID exists in the submitted data.
            if (array_key_exists($schedule->id, $submittedData)) {
                // This day was submitted, so its switch was ON ("Buka").
                $item = $submittedData[$schedule->id];
                $schedule->update([
                    'jam_buka' => $item['jam_buka'],
                    'jam_tutup' => $item['jam_tutup'],
                    'is_tutup' => false, // Explicitly set to OPEN.
                ]);
            } else {
                // This day was NOT submitted, so its switch was OFF ("Tutup").
                $schedule->update(['is_tutup' => true]); // Explicitly set to CLOSED.
            }
        }

        return redirect()->back()->with('success', 'Jadwal operasional berhasil diperbarui.');
    }

    public function getStatus()
    {
        $namaHariIndonesia = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu' // Corrected typo here
        ];

        $hariIni = $namaHariIndonesia[date('l')];
        $jadwalHariIni = JadwalOperasional::where('hari', $hariIni)->first();

        if (!$jadwalHariIni || $jadwalHariIni->is_tutup) {
            return response()->json(['status' => 'tutup', 'message' => 'Maaf, saat ini kami sedang tutup.']);
        }

        $sekarang = now()->format('H:i:s');
        if ($sekarang >= $jadwalHariIni->jam_buka && $sekarang <= $jadwalHariIni->jam_tutup) {
            return response()->json(['status' => 'buka', 'message' => 'Kami sedang buka.']);
        } else {
            return response()->json(['status' => 'tutup', 'message' => 'Maaf, saat ini kami di luar jam operasional.']);
        }
    }
}
