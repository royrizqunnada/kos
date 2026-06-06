<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

function qrAdmin(): User
{
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    return $user;
}

it('quick-renews a lease with the same terms and bills the next period', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Available->value]);
    $tenant = Tenant::factory()->create();

    $old = app(CreateLeaseAction::class)->execute(new LeaseData(
        roomId: $room->id, tenantId: $tenant->id,
        startDate: CarbonImmutable::create(2026, 3, 20),
        duration: LeaseDuration::Quarterly, monthlyPrice: 1_500_000, deposit: 0,
    ));

    actingAs(qrAdmin())
        ->post("/leases/{$old->id}/quick-renew")
        ->assertRedirect();

    // Kontrak lama berakhir, kontrak baru aktif lanjut dari hari setelah lama habis.
    expect($old->fresh()->status)->toBe(LeaseStatus::Ended);

    $new = Lease::query()->where('status', LeaseStatus::Active->value)->where('id', '!=', $old->id)->first();
    expect($new)->not->toBeNull();
    expect($new->room_id)->toBe($room->id);
    expect($new->tenant_id)->toBe($tenant->id);
    expect((float) $new->monthly_price)->toBe(1_500_000.0);
    expect($new->start_date->toDateString())->toBe('2026-06-20'); // 19 Jun + 1 hari
    // Tagihan periode baru otomatis dibuat (3 bulan × 1.5jt).
    expect((float) $new->invoices()->first()->amount)->toBe(4_500_000.0);
    expect(Room::find($room->id)->status)->toBe(RoomStatus::Occupied);
});
