<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Expense\Application\Actions\GenerateRecurringExpensesAction;
use Src\Expense\Domain\Enums\ExpenseCategory;
use Src\Expense\Domain\Models\Expense;
use Src\Expense\Domain\Models\RecurringExpense;

it('auto-records a recurring expense once per month on its day', function () {
    RecurringExpense::query()->create([
        'category' => ExpenseCategory::Internet->value,
        'description' => 'Wifi',
        'amount' => 300_000,
        'day_of_month' => 5,
        'is_active' => true,
    ]);

    $action = app(GenerateRecurringExpensesAction::class);

    // Sebelum tanggal 5 -> belum dibuat.
    expect($action->execute(CarbonImmutable::create(2026, 6, 3)))->toBe(0);
    expect(Expense::count())->toBe(0);

    // Tanggal 5 ke atas -> dibuat sekali.
    expect($action->execute(CarbonImmutable::create(2026, 6, 6)))->toBe(1);
    $expense = Expense::first();
    expect((float) $expense->amount)->toBe(300_000.0);
    expect($expense->spent_at->toDateString())->toBe('2026-06-05');

    // Dijalankan lagi di bulan yang sama -> tidak dobel (idempoten).
    expect($action->execute(CarbonImmutable::create(2026, 6, 20)))->toBe(0);
    expect(Expense::count())->toBe(1);

    // Bulan berikutnya -> dibuat lagi.
    expect($action->execute(CarbonImmutable::create(2026, 7, 10)))->toBe(1);
    expect(Expense::count())->toBe(2);
});

it('skips inactive recurring expenses', function () {
    RecurringExpense::query()->create([
        'category' => ExpenseCategory::Other->value,
        'description' => 'Langganan nonaktif',
        'amount' => 100_000,
        'day_of_month' => 1,
        'is_active' => false,
    ]);

    expect(app(GenerateRecurringExpensesAction::class)->execute(CarbonImmutable::create(2026, 6, 10)))->toBe(0);
    expect(Expense::count())->toBe(0);
});
