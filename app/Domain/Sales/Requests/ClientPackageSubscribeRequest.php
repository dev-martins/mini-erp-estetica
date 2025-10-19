<?php

namespace App\Domain\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientPackageSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('client') !== null;
    }

    public function rules(): array
    {
        return [
            'package_id' => [
                'required',
                'integer',
                Rule::exists('packages', 'id')->where('active', true),
            ],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
