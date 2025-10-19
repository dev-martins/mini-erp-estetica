<?php

namespace App\Domain\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientAppointmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('client') !== null;
    }

    public function rules(): array
    {
        $clientId = $this->user('client')?->id;

        return [
            'client_package_id' => [
                'required',
                'integer',
                Rule::exists('client_packages', 'id')
                    ->where('client_id', $clientId)
                    ->where('remaining_sessions', '>', 0),
            ],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'professional_id' => ['required', 'integer', 'exists:professionals,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipments,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_min' => ['nullable', 'integer', 'min:15', 'max:480'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
