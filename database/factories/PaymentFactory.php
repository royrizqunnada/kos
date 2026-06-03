<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Enums\PaymentMethod;
use Src\Payment\Domain\Models\Payment;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->numberBetween(8, 30) * 100000,
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'paid_at' => now(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
