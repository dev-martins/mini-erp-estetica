<?php

namespace App\Domain\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required_without:phone',
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->whereNull('deleted_at'),
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'phone')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'consent_marketing' => ['sometimes', 'boolean'],
            'source' => ['nullable', 'string', 'max:255'],
        ];
    }
}
