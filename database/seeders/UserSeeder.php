<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $admin = User::updateOrCreate(
            ['email' => 'admin@esteticaerp.com'],
            [
                'name' => 'Super Admin',
                'phone' => $faker->cellphoneNumber(),
                'role' => 'owner',
                'password' => Hash::make('senha123'),
                'email_verified_at' => now(),
            ]
        );

        $professionals = collect([
            [
                'name' => 'Laura Martins',
                'email' => 'laura@esteticaerp.com',
                'specialty' => 'Esteticista facial',
            ],
            [
                'name' => 'Camila Rodrigues',
                'email' => 'camila@esteticaerp.com',
                'specialty' => 'Massoterapeuta',
            ],
        ])->map(function (array $data) use ($faker) {
            return User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $faker->cellphoneNumber(),
                    'role' => 'professional',
                    'password' => Hash::make('senha123'),
                    'email_verified_at' => now(),
                ]
            );
        });

        User::updateOrCreate(
            ['email' => 'recepcao@esteticaerp.com'],
            [
                'name' => 'Equipe Recepção',
                'phone' => $faker->cellphoneNumber(),
                'role' => 'reception',
                'password' => Hash::make('senha123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info('Usuários criados: ' . collect([$admin])->merge($professionals)->count() + 1);
    }
}
