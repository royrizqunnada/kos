<?php

declare(strict_types=1);

namespace Src\Payment\Application\Actions;

use Illuminate\Support\Facades\DB;
use Src\Invoice\Application\Services\InvoiceStatusResolver;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Models\Payment;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class DeletePaymentAction
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private InvoiceStatusResolver $statusResolver,
    ) {}

    public function execute(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);

            $this->payments->delete($payment);

            $invoice->paid_amount = $this->payments->sumForInvoice($invoice->id);
            $invoice->status = $this->statusResolver->resolve($invoice);
            $invoice->save();
        });
    }
}
