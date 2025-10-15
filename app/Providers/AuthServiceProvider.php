<?php

namespace App\Providers;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Policies\AppointmentPolicy;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Policies\ClientPolicy;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Policies\ProductPolicy;
use App\Domain\Inventory\Policies\StockMovementPolicy;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Policies\SalePolicy;
use App\Domain\Services\Models\Service;
use App\Domain\Services\Policies\ServicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class,
        Service::class => ServicePolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Sale::class => SalePolicy::class,
        Product::class => ProductPolicy::class,
        StockMovement::class => StockMovementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();



        Passport::tokensCan([
            'read' => 'Access read-only resources',
            'write' => 'Access read and write operations',
        ]);

        if (File::isDirectory(storage_path('oauth'))) {
            Passport::loadKeysFrom(storage_path('oauth'));
        }

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
