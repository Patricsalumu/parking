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
        // Ensure helpers are loaded even if composer autoload wasn't regenerated yet
        if (! function_exists('format_dt')) {
            $helpers = app_path('helpers.php');
            if (file_exists($helpers)) {
                require_once $helpers;
            }
        }
    }
}
