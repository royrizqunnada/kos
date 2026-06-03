<?php

declare(strict_types=1);

namespace Src\Room\Domain\Data;

use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Enums\RoomType;

final readonly class RoomData
{
    /** @param array<int, string> $facilities */
    public function __construct(
        public string $roomNumber,
        public RoomType $type,
        public float $price,
        public RoomStatus $status,
        public array $facilities = [],
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            roomNumber: $data['room_number'],
            type: RoomType::from($data['type']),
            price: (float) $data['price'],
            status: RoomStatus::from($data['status'] ?? RoomStatus::Available->value),
            facilities: $data['facilities'] ?? [],
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'room_number' => $this->roomNumber,
            'type' => $this->type->value,
            'price' => $this->price,
            'status' => $this->status->value,
            'facilities' => $this->facilities,
            'description' => $this->description,
        ];
    }
}
