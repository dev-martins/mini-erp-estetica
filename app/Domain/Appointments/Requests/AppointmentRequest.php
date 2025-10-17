<?php

namespace App\Domain\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'client_package_id' => [
                'required',
                Rule::exists('client_packages', 'id')->where(fn ($query) => $query->where('client_id', $this->input('client_id'))),
            ],
            'professional_id' => ['required', 'exists:professionals,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'equipment_id' => ['nullable', 'exists:equipments,id'],
            'service_id' => ['required', 'exists:services,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:600'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled', 'no_show', 'completed'])],
            'source' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
