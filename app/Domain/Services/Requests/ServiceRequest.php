<?php

namespace App\Domain\Services\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:600'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'kit_id' => ['nullable', 'exists:service_kits,id'],
            'active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
