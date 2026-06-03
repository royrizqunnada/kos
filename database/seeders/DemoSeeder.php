<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Expense\Domain\Models\Expense;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 15 rooms, 9 of them occupied with active leases + invoices.
        $rooms = Room::factory()->count(15)->create();

        $rooms->take(9)->each(function (Room $room) {
            $room->update(['status' => RoomStatus::Occupied]);
            $tenant = Tenant::factory()->create();

            $lease = Lease::factory()->create([
                'room_id' => $room->id,
                'tenant_id' => $tenant->id,
                'monthly_price' => $room->price,
                'deposit' => $room->price,
            ]);

            $lease->invoices()->saveMany(
                \Src\Invoice\Domain\Models\Invoice::factory()->count(2)->make([
                    'amount' => $room->price,
                ])
            );
        });

        // A couple of paid invoices for revenue numbers.
        \Src\Invoice\Domain\Models\Invoice::query()->inRandomOrder()->limit(6)->get()
            ->each(fn ($inv) => $inv->update([
                'paid_amount' => $inv->amount,
                'status' => InvoiceStatus::Paid,
            ]));

        Expense::factory()->count(20)->create();
    }
}
