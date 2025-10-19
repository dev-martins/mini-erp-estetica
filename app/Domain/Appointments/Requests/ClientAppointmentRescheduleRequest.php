<?php

namespace App\Domain\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientAppointmentRescheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('client') !== null;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'professional_id' => ['sometimes', 'integer', 'exists:professionals,id'],
            'room_id' => ['sometimes', 'nullable', 'integer', 'exists:rooms,id'],
            'equipment_id' => ['sometimes', 'nullable', 'integer', 'exists:equipments,id'],
            'duration_min' => ['sometimes', 'integer', 'min:15', 'max:480'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
