<?php

namespace App\Domain\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'in:email,phone'],
            'code' => ['required', 'digits:6'],
            'login' => ['nullable', 'string', 'max:255'],
        ];
    }
}
