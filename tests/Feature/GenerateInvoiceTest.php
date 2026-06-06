<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Application\Actions\GenerateInvoiceAction;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

it('generates one invoice per lease period and is idempotent', function () {
    $lease = Lease::factory()->create(['monthly_price' => 1_500_000]);
    $action = app(GenerateInvoiceAction::class);
    $period = CarbonImmutable::now()->startOfMonth();

    $first = $action->execute($lease->load('room'), $period);
    $second = $action->execute($lease->load('room'), $period);

    expect($first)->not->toBeNull();
    expect($second)->toBeNull();
    expect($lease->invoices()->count())->toBe(1);
    expect((float) $first->amount)->toBe(1_500_000.0);
});

it('bills the full duration when months > 1', function () {
    $lease = Lease::factory()->create(['monthly_price' => 1_000_000]);
    $period = CarbonImmutable::create(2026, 1, 1);

    $invoice = app(GenerateInvoiceAction::class)->execute($lease->load('room'), $period, 3);

    expect((float) $invoice->amount)->toBe(3_000_000.0);
    expect($invoice->period_start->toDateString())->toBe('2026-01-01');
    expect($invoice->period_end->toDateString())->toBe('2026-03-31');
});

it('first invoice of a lease covers the chosen duration', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    $lease = app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id,
        tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 1, 10),
        duration: LeaseDuration::Quarterly, // 3 bulan
        monthlyPrice: 1_200_000,
        deposit: 0,
    ));

    $invoice = $lease->invoices()->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->amount)->toBe(3_600_000.0); // 3 × 1.2jt
});
