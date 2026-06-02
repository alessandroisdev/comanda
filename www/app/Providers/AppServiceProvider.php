<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\User;
use App\Services\Logging\LogSanitizerProcessor;
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
        Gate::define('viewAnyModule', function (mixed $user = null) {
            if (app()->environment('local')) {
                return true;
            }
            if ($user instanceof User) {
                return true;
            }
            if ($user instanceof Employee) {
                return $user->roles()->whereHas('permissions', function ($query) {
                    $query->where('slug', 'modules.view');
                })->exists();
            }

            return false;
        });


        // Registrar o processador global de logs no Monolog
        if ($this->app->resolved('log')) {
            $this->registerLogProcessor(resolve('log'));
        } else {
            $this->app->afterResolving('log', function ($log) {
                $this->registerLogProcessor($log);
            });
        }
    }

    private function registerLogProcessor(mixed $logManager): void
    {
        try {
            $logger = $logManager->driver();
            if (method_exists($logger, 'getMonolog')) {
                $logger->getMonolog()->pushProcessor(new LogSanitizerProcessor);
            }
        } catch (\Throwable $e) {
            // Silencia para evitar falhas em setups parciais
        }
    }
}
