<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Lease\Domain\Enums\LeaseDuration;

class StoreLeaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'start_date' => ['required', 'date'],
            'duration' => ['required', Rule::enum(LeaseDuration::class)],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'in:none,nominal,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0', $this->input('discount_type') === 'percent' ? 'max:100' : 'max:1000000000'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'generate_invoice' => ['nullable', 'boolean'],
        ];
    }
}
