<?php

declare(strict_types=1);

namespace Src\Payment\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Models\Payment;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()
            ->with(['invoice.lease.tenant', 'recordedBy'])
            ->when($filters['method'] ?? null, fn ($q, $m) => $q->where('method', $m))
            ->latest('paid_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(PaymentData $data): Payment
    {
        return Payment::query()->create([
            'invoice_id' => $data->invoiceId,
            'amount' => $data->amount,
            'method' => $data->method->value,
            'paid_at' => $data->paidAt,
            'recorded_by' => $data->recordedBy,
            'proof_path' => $data->proofPath,
            'note' => $data->note,
        ]);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->refresh();
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }

    public function sumForInvoice(int $invoiceId): string
    {
        return (string) Payment::query()->where('invoice_id', $invoiceId)->sum('amount');
    }
}
