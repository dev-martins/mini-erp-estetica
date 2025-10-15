<?php

namespace Database\Seeders;

use App\Domain\Clients\Models\Client;
use App\Domain\Loyalty\Models\LoyaltyPoint;
use App\Domain\Loyalty\Models\LoyaltyRule;
use App\Domain\Sales\Models\Sale;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        $rules = collect([
            [
                'rule_type' => 'spend',
                'value' => 1,
                'points' => 1,
                'active' => true,
                'conditions' => ['descricao' => '1 ponto para cada real gasto'],
            ],
            [
                'rule_type' => 'referral',
                'value' => 1,
                'points' => 100,
                'active' => true,
                'conditions' => ['bonus' => 'Indicação convertida em venda'],
            ],
        ])->map(fn ($rule) => LoyaltyRule::updateOrCreate(
            ['rule_type' => $rule['rule_type']],
            $rule
        ));

        $sales = Sale::with('client')->get();

        foreach ($sales as $sale) {
            if (! $sale->client) {
                continue;
            }

            $points = (int) round($sale->total_amount ?? 0);
            LoyaltyPoint::updateOrCreate(
                [
                    'client_id' => $sale->client_id,
                    'ref_id' => $sale->id,
                    'ref_type' => 'sale',
                ],
                [
                    'rule_id' => $rules->firstWhere('rule_type', 'spend')?->id,
                    'points' => $points,
                    'reason' => 'Compra realizada',
                ]
            );
        }

        $client = Client::first();
        if ($client) {
            LoyaltyPoint::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'ref_type' => 'referral_bonus',
                ],
                [
                    'rule_id' => $rules->firstWhere('rule_type', 'referral')?->id,
                    'points' => 100,
                    'reason' => 'Indicação premiada',
                ]
            );
        }

        $this->command?->info('Regras e pontos de fidelidade atribuídos.');
    }
}
