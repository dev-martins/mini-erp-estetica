<?php

namespace Database\Seeders;

use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Package;
use App\Domain\Sales\Models\Sale;
use App\Domain\Services\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('name', 'Drenagem Linfática Corporal')->first()
            ?? Service::first();

        if (! $service) {
            $this->command?->warn('Pacotes não gerados: nenhum serviço encontrado.');
            return;
        }

        $package = Package::updateOrCreate(
            ['name' => 'Plano Detox 5 sessões'],
            [
                'service_id' => $service->id,
                'sessions_count' => 5,
                'price' => 799.90,
                'expiry_days' => 180,
                'description' => 'Pacote promocional para drenagem linfática com acompanhamento personalizado.',
                'active' => true,
            ]
        );

        $sale = Sale::first();
        $client = $sale?->client;

        if ($sale && $client) {
            ClientPackage::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'package_id' => $package->id,
                ],
                [
                    'sale_id' => $sale->id,
                    'purchased_at' => $sale->sold_at ?? now(),
                    'remaining_sessions' => 4,
                    'expiry_at' => Carbon::parse($sale->sold_at ?? now())->addDays($package->expiry_days ?? 180),
                ]
            );
        }

        $this->command?->info('Pacotes criados e associados a clientes.');
    }
}
