<?php

namespace Database\Seeders;

use App\Domain\Inventory\Models\Product;
use App\Domain\Services\Models\KitItem;
use App\Domain\Services\Models\Service;
use App\Domain\Services\Models\ServiceKit;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $gel = Product::where('name', 'Gel Condutor Hipoalergênico 1L')->first();
        $mascara = Product::where('name', 'Máscara Calmante Pós-Laser')->first();
        $soro = Product::where('name', 'Soro Fisiológico 500ml')->first();
        $creme = Product::where('name', 'Creme Redutor Cafeína')->first();

        $kits = collect([
            [
                'name' => 'Kit Depilação a Laser',
                'notes' => 'Itens padronizados para sessão de depilação.',
                'items' => [
                    ['product' => $gel, 'qty' => 30],
                    ['product' => $mascara, 'qty' => 1],
                ],
            ],
            [
                'name' => 'Kit Drenagem Linfática',
                'notes' => 'Produtos para massagem modeladora e drenagem.',
                'items' => [
                    ['product' => $creme, 'qty' => 80],
                    ['product' => $soro, 'qty' => 50],
                ],
            ],
        ])->map(function (array $kitData) {
            $kit = ServiceKit::updateOrCreate(
                ['name' => $kitData['name']],
                ['notes' => $kitData['notes']]
            );

            foreach ($kitData['items'] as $item) {
                if ($item['product']) {
                    KitItem::updateOrCreate(
                        [
                            'kit_id' => $kit->id,
                            'product_id' => $item['product']->id,
                        ],
                        [
                            'qty_per_session' => $item['qty'],
                        ]
                    );
                }
            }

            return $kit;
        });

        $services = [
            [
                'name' => 'Depilação a Laser Completa',
                'category' => 'Depilação',
                'duration_min' => 60,
                'list_price' => 249.90,
                'kit' => $kits[0] ?? null,
                'description' => 'Sessão de depilação definitiva com avaliação de fototipo e cuidados pós-laser.',
            ],
            [
                'name' => 'Drenagem Linfática Corporal',
                'category' => 'Massagem',
                'duration_min' => 75,
                'list_price' => 189.00,
                'kit' => $kits[1] ?? null,
                'description' => 'Protocolo focado em retenção de líquidos e sensação de leveza.',
            ],
            [
                'name' => 'Limpeza de Pele Diamantada',
                'category' => 'Facial',
                'duration_min' => 70,
                'list_price' => 229.90,
                'kit' => null,
                'description' => 'Limpeza completa com esfoliação diamantada, máscara calmante e hidratação profunda.',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                [
                    'category' => $service['category'],
                    'duration_min' => $service['duration_min'],
                    'list_price' => $service['list_price'],
                    'kit_id' => $service['kit']?->id,
                    'description' => $service['description'],
                    'active' => true,
                ]
            );
        }

        $this->command?->info('Serviços e kits cadastrados: ' . Service::count());
    }
}
