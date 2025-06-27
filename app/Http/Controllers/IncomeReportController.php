<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class IncomeReportController extends Controller
{
    public function printPdf(Request $request)
    {
        $query = Payment::query();

        // Ambil filter dari tableFilters (Filament Table)
        $tableFilters = $request->input('tableFilters', []);
        if (isset($tableFilters['date_range']['from']) && isset($tableFilters['date_range']['until'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($tableFilters['date_range']['from'])->startOfDay(),
                Carbon::parse($tableFilters['date_range']['until'])->endOfDay(),
            ]);
        }
        if (isset($tableFilters['per_bulan']['bulan']) && isset($tableFilters['per_bulan']['tahun'])) {
            $start = Carbon::createFromDate($tableFilters['per_bulan']['tahun'], $tableFilters['per_bulan']['bulan'], 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('created_at', [$start, $end]);
        }

        // Fallback: filter by flat parameters (jaga-jaga)
        if ($request->filled('date_range')) {
            $dateRange = $request->input('date_range');
            if (isset($dateRange['from']) && isset($dateRange['until'])) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dateRange['from'])->startOfDay(),
                    Carbon::parse($dateRange['until'])->endOfDay(),
                ]);
            }
        }
        if ($request->filled('per_bulan')) {
            $perBulan = $request->input('per_bulan');
            if (isset($perBulan['bulan']) && isset($perBulan['tahun'])) {
                $start = Carbon::createFromDate($perBulan['tahun'], $perBulan['bulan'], 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $start = Carbon::createFromDate($request->tahun, $request->bulan, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('created_at', [$start, $end]);
        }
        if ($request->filled('from') && $request->filled('until')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->until)->endOfDay(),
            ]);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();
        $total = $payments->sum('amount');

        $pdf = Pdf::loadView('income-report-pdf', [
            'payments' => $payments,
            'total' => $total,
            'filters' => $request->all(),
        ]);

        return $pdf->download('laporan-pemasukan.pdf');
    }
} 