<?php

namespace Database\Seeders;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Models\Equipment;
use App\Domain\Appointments\Models\Professional;
use App\Domain\Appointments\Models\Room;
use App\Domain\Appointments\Models\SessionItem;
use App\Domain\Appointments\Models\SessionMeasure;
use App\Domain\Appointments\Models\SessionPhoto;
use App\Domain\Clients\Models\Client;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductBatch;
use App\Domain\Services\Models\Service;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $clients = Client::all();
        $professionals = Professional::with('user')->get();
        $services = Service::with('kit.items.product')->get();
        $batches = ProductBatch::all();

        if ($clients->isEmpty() || $professionals->isEmpty() || $services->isEmpty()) {
            $this->command?->warn('Nenhuma consulta gerada: verifique se clientes, profissionais e serviços existem.');
            return;
        }

        $appointments = collect();

        foreach ($clients->take(8) as $index => $client) {
            $professional = $professionals[$index % $professionals->count()];
            $service = $services[$index % $services->count()];
            $room = $professional->appointments()->exists()
                ? $professional->appointments()->latest()->first()->room
                : Room::inRandomOrder()->first();
            $equipment = Equipment::inRandomOrder()->first();

            $scheduledAt = Carbon::now()->subDays(14 - $index * 2)->setTime(10 + ($index % 3) * 2, 0);

            $appointment = Appointment::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'professional_id' => $professional->id,
                    'service_id' => $service->id,
                    'scheduled_at' => $scheduledAt,
                ],
                [
                    'room_id' => $room?->id,
                    'equipment_id' => $equipment?->id,
                    'duration_min' => $service->duration_min ?? 60,
                    'status' => $faker->randomElement(['completed', 'confirmed']),
                    'source' => $client->source ?? 'Instagram',
                    'notes' => 'Cliente orientada a evitar exposição solar nas 48h seguintes.',
                    'started_at' => $scheduledAt,
                    'ended_at' => $scheduledAt->copy()->addMinutes($service->duration_min ?? 60),
                    'confirmation_token' => null,
                    'cancellation_reason' => null,
                ]
            );

            $appointments->push($appointment);

            SessionPhoto::updateOrCreate(
                ['appointment_id' => $appointment->id, 'type' => 'before'],
                [
                    'path' => "appointments/{$appointment->id}/before.jpg",
                    'meta' => ['angle' => 'frontal'],
                ]
            );

            SessionPhoto::updateOrCreate(
                ['appointment_id' => $appointment->id, 'type' => 'after'],
                [
                    'path' => "appointments/{$appointment->id}/after.jpg",
                    'meta' => ['iluminacao' => 'studio'],
                ]
            );

            SessionMeasure::updateOrCreate(
                ['appointment_id' => $appointment->id, 'metric' => 'Cintura'],
                [
                    'value' => 74.5 - $index,
                    'unit' => 'cm',
                ]
            );

            $service->kit?->items->each(function ($item) use ($appointment, $batches) {
                $batch = $batches->firstWhere('product_id', $item->product_id);

                SessionItem::updateOrCreate(
                    [
                        'appointment_id' => $appointment->id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'quantity_used' => $item->qty_per_session,
                        'batch_id' => $batch?->id,
                    ]
                );

                if ($batch) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('current_stock', $item->qty_per_session);
                    }
                }
            });
        }

        $this->command?->info('Atendimentos criados: ' . $appointments->count());
    }
}
