<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\UserMembership;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // Timezone lokal
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $thisMonth = Carbon::now('Asia/Jakarta')->month;

        // Pendapatan Membership (1x bayar di awal)
        $dailyMembershipRevenue = Payment::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->whereIn('order_id', function ($query) {
                $query->select('order_id')->from('user_memberships');
            })
            ->sum('amount');

        $monthlyMembershipRevenue = Payment::whereMonth('created_at', $thisMonth)
            ->where('status', 'completed')
            ->whereIn('order_id', function ($query) {
                $query->select('order_id')->from('user_memberships');
            })
            ->sum('amount');

        // Pendapatan Booking Reguler (booking non-member)
        $dailyBookingRevenue = Booking::whereDate('created_at', $today)
            ->whereNotIn('order_id', function ($query) {
                $query->select('order_id')->from('user_memberships');
            })
            ->sum('price');

        $monthlyBookingRevenue = Booking::whereMonth('created_at', $thisMonth)
            ->whereNotIn('order_id', function ($query) {
                $query->select('order_id')->from('user_memberships');
            })
            ->sum('price');

        // Total Member Aktif
        $totalActiveMembers = UserMembership::where('status', 'active')->count();

        // Total Booking Bulan Ini (semua booking)
        $monthlyBookingCount = Booking::whereMonth('booking_date', $thisMonth)->count();

        return [
            Stat::make('Pemasukan Hari Ini (Member)', 'Rp ' . number_format($dailyMembershipRevenue, 0, ',', '.'))
                ->description('Total pendapatan membership hari ini')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([7, 4, 6, 8, 5, 3, 8]),

            Stat::make('Pemasukan Hari Ini (Booking)', 'Rp ' . number_format($dailyBookingRevenue, 0, ',', '.'))
                ->description('Total pendapatan booking non-member hari ini')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->chart([8, 3, 7, 5, 4, 3, 6]),

            Stat::make('Total Member Aktif', $totalActiveMembers)
                ->description('Jumlah member yang masih aktif')
                ->icon('heroicon-o-identification')
                ->color('info')
                ->chart([5, 4, 7, 8, 6, 3, 5]),

            Stat::make('Total Booking Bulan Ini', $monthlyBookingCount)
                ->description('Jumlah booking bulan ini')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->chart([6, 3, 8, 5, 7, 4, 6]),

            Stat::make('Total Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyMembershipRevenue + $monthlyBookingRevenue, 0, ',', '.'))
                ->description('Total pendapatan membership & booking bulan ini')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => sprintf(
                        "Membership: Rp %s\nBooking: Rp %s",
                        number_format($monthlyMembershipRevenue, 0, ',', '.'),
                        number_format($monthlyBookingRevenue, 0, ',', '.')
                    ),
                ]),
        ];
    }
}
