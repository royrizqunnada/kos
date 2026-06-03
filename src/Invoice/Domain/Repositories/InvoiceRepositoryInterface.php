<?php

declare(strict_types=1);

namespace Src\Invoice\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Src\Invoice\Domain\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Invoice;

    public function create(array $data): Invoice;

    public function update(Invoice $invoice, array $data): Invoice;

    public function existsForPeriod(int $leaseId, string $periodStart): bool;

    public function unpaidCount(): int;

    public function outstandingTotal(): string;

    /** @return Collection<int, Invoice> */
    public function dueOn(string $date): Collection;
}
