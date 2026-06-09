<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

function makeLeaseWithDiscount(string $type, float $value): \Src\Lease\Domain\Models\Lease
{
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    return app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id, tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 6, 1),
        duration: LeaseDuration::Quarterly, // 3 bulan
        monthlyPrice: 1_600_000, deposit: 0,
        discountType: $type, discountValue: $value,
    ));
}

it('applies a nominal discount to the invoice', function () {
    $lease = makeLeaseWithDiscount('nominal', 300_000);
    $invoice = $lease->invoices()->first();

    // Sewa 3 × 1,6jt = 4,8jt; diskon 300rb → total 4,5jt.
    expect((float) $invoice->discount)->toBe(300_000.0);
    expect((float) $invoice->amount)->toBe(4_500_000.0);
    // Item: sewa (gross) + diskon (negatif) = net.
    expect((float) $invoice->items()->sum('amount'))->toBe(4_500_000.0);
});

it('applies a percentage discount to the invoice', function () {
    $lease = makeLeaseWithDiscount('percent', 10);
    $invoice = $lease->invoices()->first();

    // 10% dari 4,8jt = 480rb → total 4,32jt.
    expect((float) $invoice->discount)->toBe(480_000.0);
    expect((float) $invoice->amount)->toBe(4_320_000.0);
});

it('has no discount when type is none', function () {
    $lease = makeLeaseWithDiscount('none', 0);
    $invoice = $lease->invoices()->first();

    expect((float) $invoice->discount)->toBe(0.0);
    expect((float) $invoice->amount)->toBe(4_800_000.0);
    expect($invoice->items()->count())->toBe(1); // tidak ada item diskon
});
