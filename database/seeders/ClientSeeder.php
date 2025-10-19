<?php

namespace Database\Seeders;

use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Models\Lead;
use App\Domain\Clients\Models\Referral;
use App\Domain\Compliance\Models\Anamnese;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $clients = collect(range(1, 10))->map(function () use ($faker) {
            $createdAt = $faker->dateTimeBetween('-6 months', 'now -10 days');
            $email = $faker->unique()->safeEmail();
            $phone = $faker->unique()->numerify('5599########');
            $verifiedAt = now()->subDays(random_int(5, 30));

            return Client::updateOrCreate(
                ['email' => $email],
                [
                    'full_name' => $faker->name(),
                    'phone' => $phone,
                    'birthdate' => $faker->dateTimeBetween('-40 years', '-18 years'),
                    'instagram' => '@' . $faker->userName(),
                    'consent_marketing' => $faker->boolean(70),
                    'source' => $faker->randomElement(['Instagram', 'Indicação', 'Google Meu Negócio']),
                    'last_appointment_at' => $createdAt,
                    'tags' => Arr::random([
                        ['VIP', 'Fidelizada'],
                        ['Potencial', 'Massagem'],
                        ['Laser', 'Recorrente'],
                        ['Recém-chegada'],
                    ]),
                    'password' => Hash::make('senha123'),
                    'email_verified_at' => $verifiedAt,
                    'phone_verified_at' => $verifiedAt,
                    'verification_code' => null,
                    'verification_code_expires_at' => null,
                    'verification_channels' => [],
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]
            );
        });

        $clients->each(function (Client $client) use ($faker) {
            Lead::updateOrCreate(
                ['phone' => $client->phone ?? $faker->unique()->numerify('5599########')],
                [
                    'name' => $client->full_name,
                    'email' => $client->email,
                    'source' => $client->source ?? 'Instagram',
                    'utm_source' => 'campanha_inicial',
                    'utm_campaign' => 'verao_lisinho',
                    'status' => $faker->randomElement(['converted', 'qualified']),
                    'client_id' => $client->id,
                    'last_contacted_at' => now()->subDays(random_int(5, 20)),
                    'notes' => 'Lead nutrido via fluxo de WhatsApp automatizado.',
                ]
            );

            Anamnese::updateOrCreate(
                ['client_id' => $client->id],
                [
                    'form_json' => [
                        'objetivo' => $faker->randomElement(['Reduzir medidas', 'Eliminar pelos', 'Relaxar']),
                        'alergias' => $faker->randomElement(['Nenhuma', 'Lactose', 'Frutos do mar']),
                        'observacoes' => 'Cliente orientada sobre cuidados pré e pós atendimento.',
                    ],
                    'signed_at' => now()->subDays(random_int(10, 60)),
                    'signer_name' => $client->full_name,
                    'signature_path' => null,
                ]
            );
        });

        if ($clients->count() >= 4) {
            Referral::updateOrCreate(
                [
                    'client_id' => $clients[2]->id,
                    'referred_by_client_id' => $clients[0]->id,
                ],
                [
                    'channel' => 'Indicação amiga',
                ]
            );

            Referral::updateOrCreate(
                [
                    'client_id' => $clients[3]->id,
                    'referred_by_client_id' => $clients[1]->id,
                ],
                [
                    'channel' => 'Parceiro estúdio de pilates',
                ]
            );
        }

        $this->command?->info('Clientes, leads e prontuários gerados.');
    }
}
