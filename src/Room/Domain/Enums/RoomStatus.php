<?php

declare(strict_types=1);

namespace Src\Room\Domain\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Booking = 'booking';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Kosong',
            self::Occupied => 'Terisi',
            self::Booking => 'Booking',
            self::Maintenance => 'Maintenance',
        };
    }
}
