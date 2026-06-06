<?php

declare(strict_types=1);

namespace Src\Payment\Application\Actions;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Invoice\Application\Services\InvoiceStatusResolver;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Models\Payment;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class UpdatePaymentAction
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private InvoiceStatusResolver $statusResolver,
    ) {}

    /** @param array<string, mixed> $fields amount, method, paid_at, note, proof_path */
    public function execute(Payment $payment, array $fields): Payment
    {
        if (isset($fields['amount']) && (float) $fields['amount'] <= 0) {
            throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
        }

        return DB::transaction(function () use ($payment, $fields) {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);

            $this->payments->update($payment, $fields);

            // Hitung ulang tagihan dari total pembayaran terbaru.
            $invoice->paid_amount = $this->payments->sumForInvoice($invoice->id);
            $invoice->status = $this->statusResolver->resolve($invoice);
            $invoice->save();

            return $payment->refresh()->load('invoice');
        });
    }
}
