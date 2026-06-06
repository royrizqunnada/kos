<?php

declare(strict_types=1);

use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Application\Actions\DeleteLeaseAction;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Enums\PaymentMethod;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

function payInvoice(Invoice $invoice, float $amount): void
{
    app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: $amount,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));
}

it('deleting a lease removes its invoices & payments and frees the room', function () {
    $lease = Lease::factory()->create();
    $roomId = $lease->room_id;
    Room::query()->whereKey($roomId)->update(['status' => RoomStatus::Occupied->value]);

    $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'amount' => 500_000]);
    payInvoice($invoice, 100_000);

    app(DeleteLeaseAction::class)->execute($lease);

    expect(Lease::find($lease->id))->toBeNull();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Payment::where('invoice_id', $invoice->id)->count())->toBe(0);
    expect(Room::find($roomId)->status)->toBe(RoomStatus::Available);
});

it('deleting an invoice removes its payments', function () {
    $invoice = Invoice::factory()->create(['amount' => 500_000]);
    payInvoice($invoice, 250_000);

    $invoice->delete();

    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Payment::where('invoice_id', $invoice->id)->count())->toBe(0);
});
