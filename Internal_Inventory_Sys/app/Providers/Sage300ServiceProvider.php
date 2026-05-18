<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Sage300Service;

class Sage300ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Sage300Service::class, function ($app) {
            return new Sage300Service();
        });
    }

    public function boot(): void
    {
        //
    }
}