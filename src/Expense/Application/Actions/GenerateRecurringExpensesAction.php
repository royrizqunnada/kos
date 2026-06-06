<?php

declare(strict_types=1);

namespace Src\Expense\Application\Actions;

use Carbon\CarbonImmutable;
use Src\Expense\Domain\Models\Expense;
use Src\Expense\Domain\Models\RecurringExpense;

final readonly class GenerateRecurringExpensesAction
{
    /**
     * Catat otomatis pengeluaran tetap (gaji penjaga, wifi, dll) untuk bulan berjalan
     * begitu tanggalnya tiba. Idempoten: satu template hanya sekali per bulan.
     */
    public function execute(?CarbonImmutable $asOf = null): int
    {
        $asOf = ($asOf ?? CarbonImmutable::now())->startOfDay();
        $created = 0;

        RecurringExpense::query()->where('is_active', true)->get()
            ->each(function (RecurringExpense $tpl) use ($asOf, &$created) {
                // Tanggal jatuh pencatatan bulan ini (clamp bila bulan pendek, mis. Feb).
                $day = min($tpl->day_of_month, $asOf->daysInMonth);

                // Belum waktunya bulan ini -> lewati.
                if ($asOf->day < $day) {
                    return;
                }

                // Sudah tercatat bulan ini -> lewati (idempoten).
                $exists = Expense::query()
                    ->where('recurring_expense_id', $tpl->id)
                    ->whereYear('spent_at', $asOf->year)
                    ->whereMonth('spent_at', $asOf->month)
                    ->exists();
                if ($exists) {
                    return;
                }

                Expense::query()->create([
                    'recurring_expense_id' => $tpl->id,
                    'category' => $tpl->category,
                    'amount' => $tpl->amount,
                    'description' => $tpl->description,
                    'spent_at' => $asOf->setDay($day)->toDateString(),
                    'recorded_by' => null,
                ]);
                $created++;
            });

        return $created;
    }
}
