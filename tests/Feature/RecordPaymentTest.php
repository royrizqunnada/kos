<?php

declare(strict_types=1);

use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Enums\PaymentMethod;

use function Pest\Laravel\artisan;

it('records a full payment and marks invoice paid', function () {
    $invoice = Invoice::factory()->create(['amount' => 1_000_000, 'paid_amount' => 0, 'status' => InvoiceStatus::Unpaid]);

    $payment = app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: 1_000_000,
        method: PaymentMethod::Transfer,
        paidAt: now()->toImmutable(),
    ));

    expect($payment->invoice_id)->toBe($invoice->id);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect((float) $invoice->fresh()->paid_amount)->toBe(1_000_000.0);
});

it('records a partial payment and marks invoice partial', function () {
    $invoice = Invoice::factory()->create(['amount' => 1_000_000, 'paid_amount' => 0, 'due_date' => now()->addDays(5), 'status' => InvoiceStatus::Unpaid]);

    app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: 400_000,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Partial);
});

it('rejects non-positive payments', function () {
    $invoice = Invoice::factory()->create(['amount' => 500_000]);

    app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: 0,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));
})->throws(RuntimeException::class);
