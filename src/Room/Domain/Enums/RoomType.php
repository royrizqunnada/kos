<?php

declare(strict_types=1);

namespace Src\Room\Domain\Enums;

enum RoomType: string
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case D = 'd';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Tipe A',
            self::B => 'Tipe B',
            self::C => 'Tipe C',
            self::D => 'Tipe D',
        };
    }
}
