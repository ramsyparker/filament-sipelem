<?php
namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\NavigationGroup;  // Import NavigationGroup

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            // Pastikan pengguna sudah login
            if (Auth::check()) {
                $user = Auth::user();

                // Cek jika pengguna memiliki peran "owner"
                if ($user->role === 'owner') {
                    Filament::registerNavigationGroups([
                        new NavigationGroup('Owner', [
                            \App\Filament\Resources\UserResource::class,
                            \App\Filament\Resources\BookingResource::class,
                            \App\Filament\Resources\FieldResource::class,
                            \App\Filament\Resources\MembershipResource::class,
                            \App\Filament\Resources\ScheduleResource::class,
                            \App\Filament\Resources\UserMembershipResource::class,
                            \App\Filament\Resources\IncomeReportResource::class,
                        ]),
                    ]);
                }

                // Cek jika pengguna memiliki peran "admin"
                if ($user->role === 'admin') {
                    Filament::registerNavigationGroups([
                        new NavigationGroup('Admin', [
                            \App\Filament\Resources\UserResource::class,
                            \App\Filament\Resources\BookingResource::class,
                            \App\Filament\Resources\FieldResource::class,
                            \App\Filament\Resources\MembershipResource::class,
                            \App\Filament\Resources\ScheduleResource::class,
                            \App\Filament\Resources\UserMembershipResource::class,
                            //\App\Filament\Resources\IncomeReportResource::class,
                        ]),
                    ]);
                }
            }
        });
    }
}
