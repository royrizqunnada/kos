<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;

/** @extends Factory<Lease> */
class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        $duration = fake()->randomElement(LeaseDuration::cases());
        $start = fake()->dateTimeBetween('-6 months', 'now');
        $end = (clone $start)->modify("+{$duration->months()} months");
        $price = fake()->numberBetween(8, 30) * 100000;

        return [
            'room_id' => Room::factory(),
            'tenant_id' => Tenant::factory(),
            'start_date' => $start,
            'end_date' => $end,
            'duration' => $duration,
            'monthly_price' => $price,
            'deposit' => $price,
            'status' => LeaseStatus::Active,
        ];
    }
}
