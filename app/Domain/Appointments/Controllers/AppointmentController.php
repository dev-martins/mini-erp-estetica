<?php

namespace App\Domain\Appointments\Controllers;

use Carbon\CarbonImmutable;
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
            filters: $request->only(['from', 'to', 'professional_id', 'client_id']),
            perPage: $request->integer('per_page', 20)
        );

        $metricsDate = $this->resolveMetricsDate($request);
        $metrics = $this->service->metricsForDate($metricsDate);

        return AppointmentResource::collection($appointments)->additional([
            'extra' => [
                'metrics' => $metrics,
            ],
        ]);
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
        $updated = $this->service->update($appointment, AppointmentData::fromRequest($request), $request->user());

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

        $updated = $this->service->setStatus($appointment, $validated['status'], $request->user());

        return AppointmentResource::make($updated);
    }

    private function resolveMetricsDate(Request $request): CarbonImmutable
    {
        $dateInput = $request->input('for_date');

        if (! $dateInput) {
            return CarbonImmutable::now();
        }

        try {
            return CarbonImmutable::parse($dateInput);
        } catch (\Throwable) {
            return CarbonImmutable::now();
        }
    }
}
