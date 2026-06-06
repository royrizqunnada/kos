<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Src\Payment\Domain\Enums\PaymentMethod;

class UpdatePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_at' => ['required', 'date'],
            'proof' => ['nullable', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
