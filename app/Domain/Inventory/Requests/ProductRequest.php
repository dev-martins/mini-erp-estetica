<?php

namespace App\Domain\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'current_stock' => ['nullable', 'numeric'],
            'expiry_control' => ['sometimes', 'boolean'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
