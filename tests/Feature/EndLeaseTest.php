<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Lease\Application\Actions\EndLeaseAction;
use Src\Lease\Domain\Data\LeaseCheckoutData;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;

it('checks out a lease, settles the deposit and frees the room', function () {
    $lease = Lease::factory()->create(['deposit' => 1_000_000]);
    $lease->room()->update(['status' => RoomStatus::Occupied->value]);

    $action = app(EndLeaseAction::class);
    $result = $action->execute($lease, new LeaseCheckoutData(
        endedAt: CarbonImmutable::parse('2026-06-05'),
        depositRefunded: 700_000,
        depositDeduction: 300_000,
        notes: 'Potong tunggakan listrik.',
    ));

    expect($result->status)->toBe(LeaseStatus::Ended);
    expect($result->ended_at->toDateString())->toBe('2026-06-05');
    expect((float) $result->deposit_refunded)->toBe(700_000.0);
    expect((float) $result->deposit_deduction)->toBe(300_000.0);
    expect($result->room->refresh()->status)->toBe(RoomStatus::Available);
});

it('rejects a settlement greater than the deposit received', function () {
    $lease = Lease::factory()->create(['deposit' => 1_000_000]);
    $action = app(EndLeaseAction::class);

    expect(fn () => $action->execute($lease, new LeaseCheckoutData(
        endedAt: CarbonImmutable::now(),
        depositRefunded: 800_000,
        depositDeduction: 300_000,
    )))->toThrow(RuntimeException::class);

    expect($lease->refresh()->status)->toBe(LeaseStatus::Active);
});

it('refuses to end a lease that is not active', function () {
    $lease = Lease::factory()->ended()->create();
    $action = app(EndLeaseAction::class);

    expect(fn () => $action->execute($lease, new LeaseCheckoutData(CarbonImmutable::now())))
        ->toThrow(RuntimeException::class);
});
