<?php

namespace App\Filament\Resources\IncomeReportResource\Pages;

use App\Filament\Resources\IncomeReportResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ListIncomeReports extends ListRecords
{
    protected static string $resource = IncomeReportResource::class;

    protected function getHeaderActions(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        return [
            ActionGroup::make([
                Action::make('print_today')
                    ->label('Cetak Hari Ini')
                    ->icon('heroicon-o-printer')
                    ->url(route('income-report.print-pdf', [
                        'from' => $today->format('Y-m-d'),
                        'until' => $today->format('Y-m-d'),
                    ]))
                    ->openUrlInNewTab(),
                Action::make('print_week')
                    ->label('Cetak Minggu Ini')
                    ->icon('heroicon-o-printer')
                    ->url(route('income-report.print-pdf', [
                        'from' => $startOfWeek->format('Y-m-d'),
                        'until' => $endOfWeek->format('Y-m-d'),
                    ]))
                    ->openUrlInNewTab(),
                Action::make('print_month')
                    ->label('Cetak Bulan Ini')
                    ->icon('heroicon-o-printer')
                    ->url(route('income-report.print-pdf', [
                        'bulan' => $today->format('m'),
                        'tahun' => $today->format('Y'),
                    ]))
                    ->openUrlInNewTab(),
                Action::make('print_year')
                    ->label('Cetak Tahun Ini')
                    ->icon('heroicon-o-printer')
                    ->url(route('income-report.print-pdf', [
                        'tahun' => $today->format('Y'),
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->icon('heroicon-o-printer')
            ->label('Print Laporan')
            ->color('warning'),
        ];
    }
}
