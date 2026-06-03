<?php

declare(strict_types=1);

namespace Src\Expense\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Expense\Domain\Models\Expense;
use Src\Expense\Domain\Repositories\ExpenseRepositoryInterface;

final class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Expense::query()
            ->with('recordedBy')
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->latest('spent_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Expense
    {
        return Expense::query()->find($id);
    }

    public function create(array $data): Expense
    {
        return Expense::query()->create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->refresh();
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function totalBetween(string $from, string $to): string
    {
        return (string) Expense::query()
            ->whereBetween('spent_at', [$from, $to])
            ->sum('amount');
    }
}
