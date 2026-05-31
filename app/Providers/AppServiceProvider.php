<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        Request::macro('wantsJson', function () {
            return true;
        });

        JsonResponse::macro('withEncoding', function () {
            return $this->withOptions(JSON_UNESCAPED_UNICODE);
        });
    }
}
