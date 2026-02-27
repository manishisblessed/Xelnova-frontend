<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Share navigation categories with marketplace views using View Composer
        view()->composer('*', \App\Http\View\Composers\MarketplaceComposer::class);
    }
}
