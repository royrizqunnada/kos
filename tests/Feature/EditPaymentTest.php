<?php

declare(strict_types=1);

use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Application\Actions\DeletePaymentAction;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Application\Actions\UpdatePaymentAction;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Enums\PaymentMethod;

function makePayment(Invoice $invoice, float $amount)
{
    return app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: $amount,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));
}

it('recalculates the invoice when a payment is edited', function () {
    $invoice = Invoice::factory()->create(['amount' => 1_000_000, 'paid_amount' => 0, 'due_date' => now()->addDays(5), 'status' => InvoiceStatus::Unpaid]);
    $payment = makePayment($invoice, 400_000);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Partial);

    app(UpdatePaymentAction::class)->execute($payment, ['amount' => 1_000_000, 'method' => 'cash', 'paid_at' => now()->toDateString()]);

    expect((float) $invoice->fresh()->paid_amount)->toBe(1_000_000.0);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
});

it('recalculates the invoice when a payment is deleted', function () {
    $invoice = Invoice::factory()->create(['amount' => 1_000_000, 'paid_amount' => 0, 'due_date' => now()->addDays(5), 'status' => InvoiceStatus::Unpaid]);
    $payment = makePayment($invoice, 1_000_000);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);

    app(DeletePaymentAction::class)->execute($payment);

    expect((float) $invoice->fresh()->paid_amount)->toBe(0.0);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Unpaid);
});
