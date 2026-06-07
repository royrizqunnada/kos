<?php

declare(strict_types=1);

use Src\Expense\Domain\Enums\ExpenseCategory;
use Src\Expense\Domain\Models\Expense;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

it('exports a complete CSV with all detail sections', function () {
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    $room = Room::factory()->create(['room_number' => '15']);
    $tenant = Tenant::factory()->create(['name' => 'SABRINA PUTRI']);
    $lease = Lease::factory()->create([
        'room_id' => $room->id, 'tenant_id' => $tenant->id,
        'start_date' => '2026-06-01', 'end_date' => '2026-08-31',
        'monthly_price' => 1_600_000, 'status' => LeaseStatus::Active,
    ]);
    $invoice = Invoice::factory()->create([
        'lease_id' => $lease->id, 'invoice_number' => 'INV/202606/0001',
        'period_start' => '2026-06-01', 'period_end' => '2026-08-31',
        'amount' => 4_800_000, 'paid_amount' => 4_800_000,
    ]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 4_800_000, 'paid_at' => '2026-06-05 09:00:00']);
    Expense::factory()->create([
        'category' => ExpenseCategory::Internet->value, 'description' => 'Wifi Juni',
        'amount' => 300_000, 'spent_at' => '2026-06-05',
    ]);

    $res = actingAs($user)->get('/reports/export/csv?from=2026-06-01&to=2026-06-30');
    $res->assertOk();
    $body = $res->streamedContent();

    expect($body)
        ->toContain('== PEMBAYARAN')
        ->toContain('== TAGIHAN')
        ->toContain('== PENGELUARAN')
        ->toContain('== KONTRAK')
        ->toContain('SABRINA PUTRI')
        ->toContain('INV/202606/0001')
        ->toContain('Wifi Juni');
});

it('prints a complete PDF report with detail sections', function () {
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    $room = Room::factory()->create(['room_number' => '15']);
    $tenant = Tenant::factory()->create(['name' => 'SABRINA PUTRI']);
    $lease = Lease::factory()->create([
        'room_id' => $room->id, 'tenant_id' => $tenant->id,
        'start_date' => '2026-06-01', 'end_date' => '2026-08-31',
        'monthly_price' => 1_600_000, 'status' => LeaseStatus::Active,
    ]);
    $invoice = Invoice::factory()->create([
        'lease_id' => $lease->id, 'invoice_number' => 'INV/202606/0001',
        'period_start' => '2026-06-01', 'period_end' => '2026-08-31',
        'amount' => 4_800_000, 'paid_amount' => 4_800_000,
    ]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 4_800_000, 'paid_at' => '2026-06-05 09:00:00']);
    Expense::factory()->create([
        'category' => ExpenseCategory::Internet->value, 'description' => 'Wifi Juni',
        'amount' => 300_000, 'spent_at' => '2026-06-05',
    ]);

    actingAs($user)->get('/reports/print?from=2026-06-01&to=2026-06-30')
        ->assertOk()
        ->assertSee('Pembayaran (Kas Masuk)')
        ->assertSee('Tagihan')
        ->assertSee('Pengeluaran')
        ->assertSee('Kontrak')
        ->assertSee('SABRINA PUTRI')
        ->assertSee('INV/202606/0001')
        ->assertSee('Wifi Juni');
});
