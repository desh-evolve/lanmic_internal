<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class BladeServiceProvider extends ServiceProvider
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
        // @role directive
        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->hasRole($role);
        });

        // @permission directive
        Blade::if('permission', function ($permission) {
            return Auth::check() && Auth::user()->hasPermission($permission);
        });

        // @anyrole directive
        Blade::if('anyrole', function ($roles) {
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });

        // @anypermission directive
        Blade::if('anypermission', function ($permissions) {
            return Auth::check() && Auth::user()->hasAnyPermission($permissions);
        });
    }
}