<?php

declare(strict_types=1);

namespace Src\Payment\Application\Actions;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Src\Invoice\Application\Services\InvoiceStatusResolver;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Models\Payment;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class RecordPaymentAction
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private InvoiceStatusResolver $statusResolver,
    ) {}

    public function execute(PaymentData $data): Payment
    {
        return DB::transaction(function () use ($data) {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($data->invoiceId);

            if ($data->amount <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }

            $payment = $this->payments->create($data);

            $invoice->paid_amount = $this->payments->sumForInvoice($invoice->id);
            $invoice->status = $this->statusResolver->resolve($invoice);
            $invoice->save();

            return $payment->load('invoice');
        });
    }
}
