<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Laravel\Passport\Console\InstallCommand::class, function ($app) {
            return $app->make(\App\Console\Commands\PassportInstallCommand::class);
        });

        $this->app->singleton('command.passport.install', function ($app) {
            return $app->make(\App\Console\Commands\PassportInstallCommand::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PassportInstallCommand::class,
            ]);
        }
    }
}
