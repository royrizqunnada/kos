<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Application\Actions\RenewLeaseAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

it('renewing ends the old lease and creates a new active one with its invoice', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    $old = app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id, tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 1, 1),
        duration: LeaseDuration::Monthly, monthlyPrice: 1_000_000, deposit: 0,
    ));

    $new = app(RenewLeaseAction::class)->execute($old, new LeaseData(
        roomId: $room->id, tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 2, 1),
        duration: LeaseDuration::Quarterly, monthlyPrice: 1_000_000, deposit: 0,
    ), true);

    expect($old->fresh()->status)->toBe(LeaseStatus::Ended);
    expect($new->status)->toBe(LeaseStatus::Active);
    expect($new->id)->not->toBe($old->id);
    expect(Room::find($room->id)->status)->toBe(RoomStatus::Occupied);
    expect((float) $new->invoices()->first()->amount)->toBe(3_000_000.0);
});
