<?php

namespace App\Domain\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSelfUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('client') !== null;
    }

    public function rules(): array
    {
        $clientId = $this->user('client')?->id;

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->ignore($clientId)
                    ->whereNull('deleted_at'),
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'phone')
                    ->ignore($clientId)
                    ->whereNull('deleted_at'),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:255'],
            'consent_marketing' => ['sometimes', 'boolean'],
            'birthdate' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
