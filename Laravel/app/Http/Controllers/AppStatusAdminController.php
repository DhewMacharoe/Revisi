<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Import File facade
use Illuminate\Support\Facades\Log;   // Import Log facade

class AppStatusAdminController extends Controller
{
    
    // Path ke file status aplikasi
    private $appStatusFilePath;

    public function __construct()
    {
        $this->appStatusFilePath = storage_path('app/app_status.json');
    }

    // Metode untuk mengubah status aplikasi
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:open,closed',
            'message' => 'nullable|string|max:500', // Pesan opsional saat ditutup
        ]);

        $newStatus = [
            'status' => $request->input('status'),
            'message' => $request->input('message', '') // Ambil pesan atau default kosong
        ];

        try {
            File::put($this->appStatusFilePath, json_encode($newStatus));
            return response()->json(['success' => true, 'message' => 'Status aplikasi berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Failed to write app_status.json: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status aplikasi. Error: ' . $e->getMessage()], 500);
        }
    }
    public function getCurrentStatus()
    {
        $appStatus = ['status' => 'open', 'message' => '']; // Default open

        if (File::exists($this->appStatusFilePath)) {
            try {
                $fileContent = File::get($this->appStatusFilePath);
                $decodedContent = json_decode($fileContent, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                    $appStatus = array_merge($appStatus, $decodedContent);
                }
            } catch (\Exception $e) {
                Log::error('Failed to read app_status.json for dashboard: ' . $e->getMessage());
            }
        } else {
            try {
                File::put($this->appStatusFilePath, json_encode($appStatus));
            } catch (\Exception $e) {
                Log::error('Failed to create app_status.json: ' . $e->getMessage());
            }
        }

        return response()->json($appStatus);
    }
}
