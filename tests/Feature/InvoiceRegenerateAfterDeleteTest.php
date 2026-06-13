<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Application\Actions\GenerateInvoiceAction;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

it('can regenerate an invoice for a period whose previous invoice was soft-deleted', function () {
    $room = Room::factory()->create();
    $tenant = Tenant::factory()->create();
    $lease = Lease::factory()->create([
        'room_id' => $room->id,
        'tenant_id' => $tenant->id,
        'start_date' => '2026-05-28',
        'end_date' => '2027-05-27',
        'monthly_price' => 1_500_000,
        'status' => LeaseStatus::Active->value,
    ]);

    $start = CarbonImmutable::parse('2026-05-28');
    $action = app(GenerateInvoiceAction::class);

    // Tagihan pertama, lalu dihapus (soft-delete) — meninggalkan baris deleted_at.
    $first = $action->execute($lease->load('room'), $start, 1);
    expect($first)->not->toBeNull();
    $first->delete();

    expect(Invoice::withTrashed()->where('lease_id', $lease->id)->count())->toBe(1);
    expect(Invoice::where('lease_id', $lease->id)->count())->toBe(0);

    // Membuat ulang periode yang sama TIDAK boleh error duplicate-key.
    $again = $action->execute($lease->load('room'), $start, 1);

    expect($again)->not->toBeNull();
    expect(Invoice::where('lease_id', $lease->id)->count())->toBe(1);
});
