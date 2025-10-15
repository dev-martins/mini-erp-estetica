<?php

namespace Database\Seeders;

use App\Domain\Appointments\Models\Equipment;
use App\Domain\Appointments\Models\Professional;
use App\Domain\Appointments\Models\Room;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $professionals = User::where('role', 'professional')->get();

        $rooms = collect([
            ['name' => 'Sala Lumière', 'notes' => 'Focada em tratamentos faciais com iluminação suave.'],
            ['name' => 'Studio Relax', 'notes' => 'Ambiente climatizado para massagens e drenagem.'],
        ])->map(fn ($data) => Room::updateOrCreate(['name' => $data['name']], [
            'notes' => $data['notes'],
            'active' => true,
        ]));

        $equipments = collect([
            ['name' => 'Luz Pulsada Prime', 'serial' => 'LP-2024-447', 'maint_cycle_days' => 90],
            ['name' => 'Radiofrequência Sculpt', 'serial' => 'RF-2023-112', 'maint_cycle_days' => 120],
        ])->map(fn ($data) => Equipment::updateOrCreate(['name' => $data['name']], [
            'serial' => $data['serial'],
            'maint_cycle_days' => $data['maint_cycle_days'],
            'last_maintenance_at' => now()->subDays(random_int(15, 40)),
        ]));

        $professionals->each(function (User $user) use ($faker) {
            Professional::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $user->name,
                    'specialty' => $faker->randomElement(['Estética facial', 'Massoterapia', 'Depilação a laser']),
                    'commission_type' => 'percentage',
                    'commission_value' => 20,
                    'work_hours' => [
                        'segunda-sexta' => ['08:00-12:00', '13:00-18:00'],
                        'sábado' => ['09:00-14:00'],
                    ],
                    'active' => true,
                ]
            );
        });

        $this->command?->info("Profissionais cadastrados: {$professionals->count()} | Salas: {$rooms->count()} | Equipamentos: {$equipments->count()}");
    }
}