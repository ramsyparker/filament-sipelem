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

        // Handle new form parameters
        if ($request->filled('from') && $request->filled('until')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->until)->endOfDay(),
            ]);
        } elseif ($request->filled('bulan') && $request->filled('tahun')) {
            $start = Carbon::createFromDate($request->tahun, $request->bulan, 1)->startOfMonth();
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

        $payments = $query->orderBy('created_at', 'desc')->get();
        $total = $payments->sum('amount');

        // Calculate additional statistics
        $totalTransactions = $payments->count();
        $averageAmount = $totalTransactions > 0 ? $total / $totalTransactions : 0;
        $highestAmount = $payments->max('amount') ?? 0;
        $lowestAmount = $payments->min('amount') ?? 0;

        // Group by date for daily summary
        $dailySummary = $payments->groupBy(function ($payment) {
            return $payment->created_at->format('Y-m-d');
        })->map(function ($dayPayments) {
            return [
                'count' => $dayPayments->count(),
                'total' => $dayPayments->sum('amount'),
                'date' => $dayPayments->first()->created_at->format('d F Y'),
            ];
        });

        $pdf = Pdf::loadView('income-report-pdf', [
            'payments' => $payments,
            'total' => $total,
            'filters' => $request->all(),
            'totalTransactions' => $totalTransactions,
            'averageAmount' => $averageAmount,
            'highestAmount' => $highestAmount,
            'lowestAmount' => $lowestAmount,
            'dailySummary' => $dailySummary,
        ]);

        return $pdf->download('laporan-pemasukan.pdf');
    }
} 