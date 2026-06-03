<?php

declare(strict_types=1);

namespace Src\Payment\Domain\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case EWallet = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::EWallet => 'E-Wallet',
        };
    }
}
