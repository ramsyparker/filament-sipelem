<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
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
        //
        Route::middleware('web')
        ->group(function () {
            //
        });

    // ⬇️ Daftarkan alias middleware di sini
    Route::aliasMiddleware('admin.owner.only', AdminOrOwnerOnly::class);
    }
}
