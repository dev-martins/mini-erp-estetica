<?php

namespace App\Domain\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id ?? null;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email,' . $clientId],
            'birthdate' => ['nullable', 'date'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'consent_marketing' => ['sometimes', 'boolean'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
        ];
    }
}
