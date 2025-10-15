<?php

namespace Database\Seeders;

use App\Domain\Clients\Models\Client;
use App\Domain\Marketing\Models\CampaignEvent;
use App\Domain\Marketing\Models\Coupon;
use App\Domain\Marketing\Models\MarketingCampaign;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $coupon = Coupon::updateOrCreate(
            ['code' => 'BEMVINDA10'],
            [
                'type' => 'percentage',
                'value' => 10,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addMonth(),
                'max_uses' => 100,
                'used_count' => 12,
                'min_purchase_amount' => 120,
                'active' => true,
            ]
        );

        $campaign = MarketingCampaign::updateOrCreate(
            ['name' => 'Reativação Pós-Verão'],
            [
                'channel' => 'WhatsApp',
                'budget' => 500,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(20),
                'utm_source' => 'whatsapp',
                'utm_campaign' => 'reativacao_pos_verao',
                'status' => 'running',
            ]
        );

        $clients = Client::take(5)->get();

        foreach ($clients as $client) {
            CampaignEvent::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'client_id' => $client->id,
                    'event' => 'mensagem_enviada',
                ],
                [
                    'at' => now()->subDays(random_int(1, 4)),
                    'meta_json' => json_encode([
                        'conteudo' => 'Convite para sessão de manutenção',
                        'coupon' => $coupon->code,
                    ]),
                ]
            );
        }

        $this->command?->info('Campanha de marketing, cupons e eventos gerados.');
    }
}
