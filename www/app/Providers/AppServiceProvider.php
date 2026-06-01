<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        Gate::define('viewAnyModule', function (mixed $user) {
            if ($user instanceof \App\Models\User) {
                return true;
            }
            if ($user instanceof \App\Models\Employee) {
                return $user->roles()->whereHas('permissions', function ($query) {
                    $query->where('slug', 'modules.view');
                })->exists();
            }
            return false;
        });
    }
}

