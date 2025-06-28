<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestBookings;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            LatestBookings::class,
        ];
    }

    public function getColumns(): int
    {
        return 3;
    }
}