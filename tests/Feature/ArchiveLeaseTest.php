<?php

declare(strict_types=1);

use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Application\Actions\DeleteLeaseAction;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Enums\PaymentMethod;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

use function Pest\Laravel\seed;

function arsipAdmin(): User
{
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    return $user;
}

function arsipPay(Invoice $invoice, float $amount): void
{
    app(RecordPaymentAction::class)->execute(new PaymentData(
        invoiceId: $invoice->id,
        amount: $amount,
        method: PaymentMethod::Cash,
        paidAt: now()->toImmutable(),
    ));
}

it('soft-deletes a lease with its invoices & payments (recoverable)', function () {
    $lease = Lease::factory()->create(['status' => LeaseStatus::Active]);
    Room::query()->whereKey($lease->room_id)->update(['status' => RoomStatus::Occupied->value]);

    $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'amount' => 500_000]);
    arsipPay($invoice, 100_000);
    $paymentId = Payment::where('invoice_id', $invoice->id)->value('id');

    app(DeleteLeaseAction::class)->execute($lease);

    // Hilang dari query biasa, tapi masih ada di arsip.
    expect(Lease::find($lease->id))->toBeNull();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Payment::find($paymentId))->toBeNull();

    expect(Lease::withTrashed()->find($lease->id))->not->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id))->not->toBeNull();
    expect(Payment::withTrashed()->find($paymentId))->not->toBeNull();

    // Kamar dibebaskan.
    expect(Room::find($lease->room_id)->status)->toBe(RoomStatus::Available);
});

it('restores an archived lease, its invoices, payments and re-occupies the room', function () {
    $lease = Lease::factory()->create(['status' => LeaseStatus::Active]);
    Room::query()->whereKey($lease->room_id)->update(['status' => RoomStatus::Occupied->value]);
    $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'amount' => 500_000]);
    arsipPay($invoice, 100_000);
    $paymentId = Payment::where('invoice_id', $invoice->id)->value('id');

    app(DeleteLeaseAction::class)->execute($lease);

    $this->actingAs(arsipAdmin())
        ->post("/leases/{$lease->id}/restore")
        ->assertRedirect(route('leases.archive'));

    expect(Lease::find($lease->id))->not->toBeNull();
    expect(Invoice::find($invoice->id))->not->toBeNull();
    expect(Payment::find($paymentId))->not->toBeNull();
    expect(Room::find($lease->room_id)->status)->toBe(RoomStatus::Occupied);
});

it('permanently deletes an archived lease (cascade hard delete)', function () {
    $lease = Lease::factory()->create(['status' => LeaseStatus::Active]);
    $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'amount' => 500_000]);
    arsipPay($invoice, 100_000);
    $paymentId = Payment::where('invoice_id', $invoice->id)->value('id');

    app(DeleteLeaseAction::class)->execute($lease);

    $this->actingAs(arsipAdmin())
        ->delete("/leases/{$lease->id}/force")
        ->assertRedirect(route('leases.archive'));

    expect(Lease::withTrashed()->find($lease->id))->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id))->toBeNull();
    expect(Payment::withTrashed()->find($paymentId))->toBeNull();
});
