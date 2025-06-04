<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Get all reports.
     */
    public function index()
    {
        $laporans = Laporan::all();
        return response()->json($laporans);
    }

    /**
     * Get a specific report by ID.
     */
    public function show($id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json($laporan);
    }

    /**
     * Create a new report.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'total_income' => 'required|numeric',
            'total_orders' => 'required|integer',
            'status' => 'required|string',
        ]);

        $laporan = Laporan::create([
            'report_date' => $request->input('report_date'),
            'total_income' => $request->input('total_income'),
            'total_orders' => $request->input('total_orders'),
            'status' => $request->input('status'),
        ]);

        return response()->json($laporan, 201);
    }

    /**
     * Update an existing report.
     */
    public function update(Request $request, $id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $laporan->update([
            'report_date' => $request->input('report_date'),
            'total_income' => $request->input('total_income'),
            'total_orders' => $request->input('total_orders'),
            'status' => $request->input('status'),
        ]);

        return response()->json($laporan);
    }

    /**
     * Delete a report.
     */
    public function destroy($id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $laporan->delete();
        return response()->json(['message' => 'Report deleted']);
    }
}
