<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Pemesanan;
use App\Models\Menu;
use App\Models\DetailPemesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Set default period and date range
        $period = $request->period ?? 'daily';
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get orders within date range
        $orders = Pemesanan::with(['pelanggan', 'detailPemesanan.menu'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereIn('status', ['dibayar', 'diproses', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total income and orders
        $totalIncome = $orders->sum('total_harga');
        $totalOrders = $orders->count();

        // Generate reports based on period
        $reports = [];

        if ($period == 'daily') {
            // Group by day
            $dateRange = new \DatePeriod(
                $startDate->copy()->startOfDay(),
                new \DateInterval('P1D'),
                $endDate->copy()->endOfDay()
            );

            foreach ($dateRange as $date) {
                $dayOrders = $orders->filter(function ($order) use ($date) {
                    return $order->created_at->format('Y-m-d') == $date->format('Y-m-d');
                });

                $reports[] = [
                    'period' => $date->format('d M Y'),
                    'total_orders' => $dayOrders->count(),
                    'total_income' => $dayOrders->sum('total_harga'),
                ];
            }
        } else {
            // Group by month
            $dateRange = new \DatePeriod(
                $startDate->copy()->startOfMonth(),
                new \DateInterval('P1M'),
                $endDate->copy()->endOfMonth()
            );

            foreach ($dateRange as $date) {
                $monthStart = $date->format('Y-m-01');
                $monthEnd = $date->format('Y-m-t');

                $monthOrders = $orders->filter(function ($order) use ($monthStart, $monthEnd) {
                    $orderDate = $order->created_at->format('Y-m-d');
                    return $orderDate >= $monthStart && $orderDate <= $monthEnd;
                });

                $reports[] = [
                    'period' => $date->format('M Y'),
                    'total_orders' => $monthOrders->count(),
                    'total_income' => $monthOrders->sum('total_harga'),
                ];
            }
        }

        return view('reports.index', compact('period', 'startDate', 'endDate', 'reports', 'orders', 'totalIncome', 'totalOrders'));
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get orders within date range
        $orders = Pemesanan::with(['pelanggan', 'detailpemesanan.menu'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereIn('status', ['dibayar', 'diproses', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total income and orders
        $totalIncome = $orders->sum('total_harga');
        $totalOrders = $orders->count();

        $pdf = PDF::loadView('reports.pdf', compact('startDate', 'endDate', 'orders', 'totalIncome', 'totalOrders'));
        return $pdf->download('laporan-' . $startDate->format('d-m-Y') . '-sampai-' . $endDate->format('d-m-Y') . '.pdf');
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Implementasi export Excel akan ditambahkan nanti
        return redirect()->back()->with('error', 'Fitur export Excel belum tersedia.');
    }

    /**
     * Generate receipt for an order
     */
    public function receipt($pesanan)
    {
        $order = Pemesanan::with(['pelanggan', 'detailpemesanan.menu'])->findOrFail($pesanan);

        $pdf = PDF::loadView('reports.receipt', compact('order'));
        return $pdf->stream('struk-pesanan-' . $order->id . '.pdf');
    }
}
