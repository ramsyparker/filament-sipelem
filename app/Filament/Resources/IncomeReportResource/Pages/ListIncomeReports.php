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
        // Determine the correct route based on current panel
        $currentPanel = request()->segment(1);
        $routePrefix = $currentPanel === 'owner' ? 'filament.owner.resources.income-reports.print' : 'filament.admin.resources.income-reports.print';
        
        return [
            Action::make('print_report')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(route($routePrefix)),
        ];
    }
}
