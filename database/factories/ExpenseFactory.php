<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Expense\Domain\Enums\ExpenseCategory;
use Src\Expense\Domain\Models\Expense;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'amount' => fake()->numberBetween(1, 20) * 100000,
            'description' => fake()->sentence(3),
            'spent_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
