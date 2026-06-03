<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Enums;

enum LeaseStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Active => 'Aktif',
            self::Ended => 'Berakhir',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
