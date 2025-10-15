<?php

namespace Database\Seeders;

use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductBatch;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Models\Supplier;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $admin = User::where('role', 'owner')->first();

        $suppliers = collect([
            [
                'name' => 'BioEssência Distribuidora',
                'phone' => $faker->phoneNumber(),
                'email' => 'contato@bioessencia.com.br',
                'whatsapp' => $faker->cellphoneNumber(),
                'notes' => 'Fornece insumos naturais e dermocosméticos.',
            ],
            [
                'name' => 'Dermapower Brasil',
                'phone' => $faker->phoneNumber(),
                'email' => 'suporte@dermapower.com',
                'whatsapp' => $faker->cellphoneNumber(),
                'notes' => 'Equipamentos certificados pela ANVISA.',
            ],
        ])->map(fn ($data) => Supplier::updateOrCreate(['email' => $data['email']], $data));

        $products = collect([
            [
                'name' => 'Gel Condutor Hipoalergênico 1L',
                'unit' => 'ml',
                'cost_per_unit' => 0.09,
                'min_stock' => 500,
                'expiry_control' => true,
                'supplier_id' => $suppliers[0]->id,
            ],
            [
                'name' => 'Máscara Calmante Pós-Laser',
                'unit' => 'un',
                'cost_per_unit' => 18.5,
                'min_stock' => 20,
                'expiry_control' => true,
                'supplier_id' => $suppliers[0]->id,
            ],
            [
                'name' => 'Soro Fisiológico 500ml',
                'unit' => 'ml',
                'cost_per_unit' => 0.04,
                'min_stock' => 300,
                'expiry_control' => true,
                'supplier_id' => $suppliers[0]->id,
            ],
            [
                'name' => 'Creme Redutor Cafeína',
                'unit' => 'g',
                'cost_per_unit' => 0.12,
                'min_stock' => 400,
                'expiry_control' => false,
                'supplier_id' => $suppliers[1]->id,
            ],
        ])->map(function ($data) {
            return Product::updateOrCreate(
                ['name' => $data['name']],
                $data + ['current_stock' => 0, 'active' => true]
            );
        });

        $batches = $products->map(function (Product $product) use ($faker) {
            return ProductBatch::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'batch_code' => strtoupper($faker->bothify('Lote-??###')),
                ],
                [
                    'qty' => $quantity = $faker->numberBetween(200, 1200),
                    'expiry_date' => $product->expiry_control ? now()->addMonths($faker->numberBetween(6, 18)) : null,
                    'unit_cost' => $product->cost_per_unit,
                ]
            );
        });

        $products->each(function (Product $product) use ($batches) {
            $batch = $batches->firstWhere('product_id', $product->id);
            if ($batch) {
                $product->update(['current_stock' => $batch->qty]);
            }
        });

        $stockMovements = $batches->map(function (ProductBatch $batch) use ($admin) {
            return StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => 'in',
                'qty' => $batch->qty,
                'unit_cost' => $batch->unit_cost,
                'reason' => 'Entrada inicial de estoque',
                'meta' => [
                    'documento' => 'NF-e ' . random_int(1000, 9999),
                ],
                'created_by' => $admin?->id,
            ]);
        });

        $this->command?->info('Estoque inicial gerado: ' . $products->count() . ' produtos, ' . $stockMovements->count() . ' movimentos.');
    }
}
