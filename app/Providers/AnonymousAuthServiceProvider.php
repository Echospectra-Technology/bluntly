<?php

namespace App\Providers;

use App\Auth\AnonymousUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AnonymousAuthServiceProvider extends ServiceProvider
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
        Auth::provider('anonymous', function ($app, array $config) {
            return new AnonymousUserProvider();
        });
    }
}
