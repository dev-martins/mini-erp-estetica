<?php

namespace App\Domain\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'sold_at' => ['nullable', 'date'],
            'channel' => ['required', Rule::in(['pos', 'online', 'phone'])],
            'source' => ['nullable', 'string', 'max:120'],
            'payment_status' => ['required', Rule::in(['pending', 'partial', 'paid', 'refunded'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::in(['service', 'product', 'package'])],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.total' => ['required', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required', Rule::in(['pix', 'card', 'cash', 'transfer'])],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.status' => ['nullable', Rule::in(['pending', 'processing', 'paid', 'failed'])],
            'payments.*.paid_at' => ['nullable', 'date'],
            'payments.*.tx_ref' => ['nullable', 'string', 'max:190'],
        ];
    }
}
