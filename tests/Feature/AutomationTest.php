<?php

declare(strict_types=1);

use Src\Invoice\Application\Actions\RefreshInvoiceStatusesAction;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Application\Actions\EndExpiredLeasesAction;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

it('marks past-due unpaid invoices as overdue automatically', function () {
    $invoice = Invoice::factory()->create([
        'amount' => 500_000, 'paid_amount' => 0,
        'due_date' => now()->subDays(3), 'status' => InvoiceStatus::Unpaid,
    ]);

    $changed = app(RefreshInvoiceStatusesAction::class)->execute();

    expect($changed)->toBeGreaterThanOrEqual(1);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Overdue);
});

it('auto-ends expired active leases and frees the room', function () {
    $lease = Lease::factory()->create(['end_date' => now()->subDay(), 'status' => LeaseStatus::Active]);
    $roomId = $lease->room_id;
    Room::query()->whereKey($roomId)->update(['status' => RoomStatus::Occupied->value]);

    $count = app(EndExpiredLeasesAction::class)->execute();

    expect($count)->toBeGreaterThanOrEqual(1);
    expect($lease->fresh()->status)->toBe(LeaseStatus::Ended);
    expect(Room::find($roomId)->status)->toBe(RoomStatus::Available);
});
