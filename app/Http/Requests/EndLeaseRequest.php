<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndLeaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ended_at' => ['nullable', 'date'],
            'deposit_refunded' => ['nullable', 'numeric', 'min:0'],
            'deposit_deduction' => ['nullable', 'numeric', 'min:0'],
            'checkout_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
