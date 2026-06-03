<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;
use Src\Room\Domain\Models\Room;

/** @extends Factory<Room> */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_number' => 'K-'.fake()->unique()->numberBetween(100, 999),
            'type' => fake()->randomElement(RoomType::cases()),
            'price' => fake()->numberBetween(8, 30) * 100000,
            'status' => RoomStatus::Available,
            'facilities' => fake()->randomElements(
                ['AC', 'Wi-Fi', 'Kamar Mandi Dalam', 'Lemari', 'Kasur', 'Meja', 'Air Panas'],
                fake()->numberBetween(2, 5)
            ),
            'description' => fake()->sentence(),
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn () => ['status' => RoomStatus::Occupied]);
    }
}
