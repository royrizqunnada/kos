<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Enums;

enum LeaseDuration: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';

    public function months(): int
    {
        return match ($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::SemiAnnual => 6,
            self::Annual => 12,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
            self::Quarterly => '3 Bulanan',
            self::SemiAnnual => '6 Bulanan',
            self::Annual => 'Tahunan',
        };
    }
}
