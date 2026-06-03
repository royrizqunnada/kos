<?php

declare(strict_types=1);

namespace Src\Expense\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Expense\Domain\Models\Expense;

interface ExpenseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Expense;

    public function create(array $data): Expense;

    public function update(Expense $expense, array $data): Expense;

    public function delete(Expense $expense): void;

    public function totalBetween(string $from, string $to): string;
}
