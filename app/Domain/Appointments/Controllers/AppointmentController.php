<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\DTOs\AppointmentData;
use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Requests\AppointmentRequest;
use App\Domain\Appointments\Resources\AppointmentResource;
use App\Domain\Appointments\Services\AppointmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service)
    {
        $this->authorizeResource(Appointment::class, 'appointment');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = $this->service->list(
            filters: $request->only(['from', 'to', 'professional_id']),
            perPage: $request->integer('per_page', 20)
        );

        return AppointmentResource::collection($appointments);
    }

    public function store(AppointmentRequest $request)
    {
        $appointment = $this->service->create(AppointmentData::fromRequest($request));

        return AppointmentResource::make(
            $appointment->load(['client', 'professional.user', 'service', 'clientPackage.package.service'])
        )
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Appointment $appointment)
    {
        return AppointmentResource::make(
            $appointment->load(['client', 'professional.user', 'service', 'clientPackage.package.service'])
        );
    }

    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $updated = $this->service->update($appointment, AppointmentData::fromRequest($request));

        return AppointmentResource::make(
            $updated->load(['client', 'professional.user', 'service', 'clientPackage.package.service'])
        );
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->noContent();
    }

    public function setStatus(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,no_show,completed'],
        ]);

        $updated = $this->service->setStatus($appointment, $validated['status']);

        return AppointmentResource::make($updated);
    }
}
