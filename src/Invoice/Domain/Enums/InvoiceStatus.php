<?php

declare(strict_types=1);

namespace Src\Invoice\Domain\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Unpaid => 'Belum Dibayar',
            self::Partial => 'Sebagian',
            self::Paid => 'Lunas',
            self::Overdue => 'Telat',
        };
    }

    public function isSettled(): bool
    {
        return $this === self::Paid;
    }
}
