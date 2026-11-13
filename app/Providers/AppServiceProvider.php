<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFour();

        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

        if (
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->isSecure() ||
            (config('app.url') && strpos(config('app.url'), 'https://') === 0)
        ) {
            URL::forceScheme('https');
        }
    }
}
