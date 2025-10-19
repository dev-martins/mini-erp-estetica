<?php

namespace App\Domain\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'phone')
                    ->ignore($clientId)
                    ->whereNull('deleted_at'),
            ],
            'email' => [
                'required_without:phone',
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')
                    ->ignore($clientId)
                    ->whereNull('deleted_at'),
            ],
            'birthdate' => ['nullable', 'date'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'consent_marketing' => ['sometimes', 'boolean'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }
}
