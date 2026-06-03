<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            if (
                $user->hasRole('Superadmin') ||
                $user->hasRole('Super Admin') ||
                $user->hasRole('superadmin') ||
                $user->hasRole('super admin')
            ) {
                return true;
            }

            return null;
        });
    }
}