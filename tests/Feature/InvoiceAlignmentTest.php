<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Application\Actions\GenerateDueInvoicesAction;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

it('aligns the first invoice with the contract dates (mid-month start)', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    $lease = app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id,
        tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 3, 15),
        duration: LeaseDuration::Quarterly, // 3 bulan
        monthlyPrice: 1_600_000,
        deposit: 0,
    ));

    $invoice = $lease->invoices()->first();

    // Periode tagihan = persis masa kontrak (15 Mar – 14 Jun), bukan 1 Mar – 31 Mei.
    expect($invoice->period_start->toDateString())->toBe('2026-03-15');
    expect($invoice->period_end->toDateString())->toBe('2026-06-14');
    expect($invoice->period_end->toDateString())->toBe($lease->end_date->toDateString());
    // Jatuh tempo 5 hari setelah mulai.
    expect($invoice->due_date->toDateString())->toBe('2026-03-20');
});

it('does not double-bill inside the contract term', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    $lease = app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id,
        tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 3, 15),
        duration: LeaseDuration::Quarterly,
        monthlyPrice: 1_600_000,
        deposit: 0,
    ));

    // Jalankan generator di tengah masa kontrak (misal 6 Jun) — tidak boleh nambah tagihan.
    $created = app(GenerateDueInvoicesAction::class)->execute(CarbonImmutable::create(2026, 6, 6));

    expect($created)->toBe(0);
    expect($lease->invoices()->count())->toBe(1);
});
