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
        // Register legacy migration commands when running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\MigrateLegacyClients::class,
                \App\Console\Commands\MigrateLegacyProducts::class,
                \App\Console\Commands\MigrateLegacyServices::class,
                \App\Console\Commands\MigrateLegacyServiceOrders::class,
                \App\Console\Commands\MigrateLegacyServiceOrderItems::class,
                \App\Console\Commands\RecalculateServiceOrderTotals::class,
                \App\Console\Commands\ReportLegacyMigration::class,
                \App\Console\Commands\BackfillServiceOrderCodes::class,
            ]);
        }
    }
}
