<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;
use App\Http\Middleware\OwnerOnly; // Menggunakan OwnerOnly middleware
use App\Http\Middleware\AdminOrOwnerOnly;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        //
        Route::middleware('web')
        ->group(function () {
            //
        });

    // ⬇️ Daftarkan alias middleware di sini
    Route::aliasMiddleware('admin.owner.only', AdminOrOwnerOnly::class);
    }
}
