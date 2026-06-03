<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Models\Lease;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', 'now');
        $end = (clone $start)->modify('+1 month -1 day');
        $amount = fake()->numberBetween(8, 30) * 100000;

        return [
            'lease_id' => Lease::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('########'),
            'period_start' => $start,
            'period_end' => $end,
            'due_date' => (clone $start)->modify('+5 days'),
            'amount' => $amount,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Unpaid,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_amount' => $attrs['amount'],
            'status' => InvoiceStatus::Paid,
        ]);
    }
}
