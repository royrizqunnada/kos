<?php

declare(strict_types=1);

namespace Src\Room\Domain\Enums;

enum RoomType: string
{
    case Standard = 'standard';
    case Deluxe = 'deluxe';
    case Suite = 'suite';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Deluxe => 'Deluxe',
            self::Suite => 'Suite',
        };
    }
}
