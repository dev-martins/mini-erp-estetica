<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Requests\ClientAppointmentRescheduleRequest;
use App\Domain\Appointments\Requests\ClientAppointmentStoreRequest;
use App\Domain\Appointments\Resources\AppointmentResource;
use App\Domain\Appointments\Services\AppointmentService;
use App\Domain\Clients\Models\Client;
use App\Domain\Sales\Models\ClientPackage;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\Appointments\DTOs\AppointmentData;

class ClientAppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments)
    {
    }

    public function index(Request $request)
    {
        /** @var \App\Domain\Clients\Models\Client $client */
        $client = $request->user('client');

        $filters = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'client_id' => $client->id,
        ];

        if ($request->filled('status')) {
            $filters['status'] = array_filter(array_map('trim', explode(',', $request->input('status'))));
        }

        $appointments = $this->appointments->list(
            $filters,
            $request->integer('per_page', 15)
        );

        return AppointmentResource::collection($appointments);
    }

    public function store(ClientAppointmentStoreRequest $request)
    {
        /** @var \App\Domain\Clients\Models\Client $client */
        $client = $request->user('client');

        $package = ClientPackage::query()
            ->where('id', $request->integer('client_package_id'))
            ->where('client_id', $client->id)
            ->firstOrFail();

        if ($package->remaining_sessions <= 0) {
            throw ValidationException::withMessages([
                'client_package_id' => 'Não há sessões disponíveis neste pacote.',
            ]);
        }

        $scheduledAt = CarbonImmutable::parse($request->input('scheduled_at'));
        if ($package->expiry_at && $scheduledAt->greaterThan(CarbonImmutable::parse($package->expiry_at))) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'A nova data ultrapassa a validade do pacote selecionado.',
            ]);
        }

        $data = new AppointmentData(
            clientId: $client->id,
            clientPackageId: $package->id,
            professionalId: (int) $request->input('professional_id'),
            roomId: $request->input('room_id'),
            equipmentId: $request->input('equipment_id'),
            serviceId: (int) $request->input('service_id'),
            scheduledAt: $scheduledAt->toIso8601String(),
            durationMin: (int) $request->input('duration_min', 60),
            status: 'pending',
            source: 'cliente',
            notes: $request->input('notes'),
        );

        $appointment = $this->appointments->create($data);

        return AppointmentResource::make($appointment)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function reschedule(
        ClientAppointmentRescheduleRequest $request,
        Appointment $appointment
    ) {
        /** @var \App\Domain\Clients\Models\Client $client */
        $client = $request->user('client');
        $this->ensureOwnership($appointment, $client);

        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Somente atendimentos pendentes ou confirmados podem ser remarcados.',
            ]);
        }

        $scheduledAt = CarbonImmutable::parse($request->input('scheduled_at'));

        $data = new AppointmentData(
            clientId: $client->id,
            clientPackageId: (int) $appointment->client_package_id,
            professionalId: (int) $request->input('professional_id', $appointment->professional_id),
            roomId: $request->has('room_id') ? $request->input('room_id') : $appointment->room_id,
            equipmentId: $request->has('equipment_id') ? $request->input('equipment_id') : $appointment->equipment_id,
            serviceId: (int) $appointment->service_id,
            scheduledAt: $scheduledAt->toIso8601String(),
            durationMin: (int) $request->input('duration_min', $appointment->duration_min ?? 60),
            status: $appointment->status,
            source: $appointment->source,
            notes: $request->input('notes', $appointment->notes),
        );

        $updated = $this->appointments->update($appointment, $data);

        return AppointmentResource::make($updated);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        /** @var \App\Domain\Clients\Models\Client $client */
        $client = $request->user('client');
        $this->ensureOwnership($appointment, $client);

        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Somente atendimentos pendentes ou confirmados podem ser cancelados.',
            ]);
        }

        $updated = $this->appointments->setStatus($appointment, 'cancelled');

        return AppointmentResource::make($updated);
    }

    protected function ensureOwnership(Appointment $appointment, Client $client): void
    {
        if ($appointment->client_id !== $client->id) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
