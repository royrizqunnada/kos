<?php

declare(strict_types=1);

namespace Src\Invoice\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

final class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['lease.room', 'lease.tenant'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('invoice_number', 'ilike', "%$s%"))
            ->orderByDesc('due_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Invoice
    {
        return Invoice::query()->with(['lease.room', 'lease.tenant', 'items', 'payments.recordedBy'])->find($id);
    }

    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->refresh();
    }

    public function existsForPeriod(int $leaseId, string $periodStart): bool
    {
        return Invoice::query()
            ->where('lease_id', $leaseId)
            ->whereDate('period_start', $periodStart)
            ->exists();
    }

    public function unpaidCount(): int
    {
        return Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
            ->count();
    }

    public function outstandingTotal(): string
    {
        return (string) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
            ->selectRaw('coalesce(sum(amount - paid_amount), 0) as total')
            ->value('total');
    }

    public function dueOn(string $date): Collection
    {
        return Invoice::query()
            ->with(['lease.room', 'lease.tenant'])
            ->whereDate('due_date', $date)
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
            ->get();
    }
}
