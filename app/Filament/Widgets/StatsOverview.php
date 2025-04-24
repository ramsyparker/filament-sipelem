<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\UserMembership;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Booking Hari Ini', Booking::whereDate('booking_date', Carbon::today())->count())
                ->description('Jumlah booking hari ini')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->chart([7, 4, 6, 8, 5, 3, 8]),

            Stat::make('Member Aktif', UserMembership::where('status', 'active')->count())
                ->description('Total member yang aktif')
                ->icon('heroicon-o-users')
                ->color('primary')
                ->chart([8, 3, 7, 5, 4, 3, 6]),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format(Booking::whereMonth('created_at', Carbon::now()->month)->sum('price'), 0, ',', '.'))
                ->description('Total pendapatan bulan ini')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->chart([3, 5, 7, 4, 8, 6, 5]),
        ];
    }
}