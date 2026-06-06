<?php

declare(strict_types=1);

namespace Src\Expense\Domain\Enums;

enum ExpenseCategory: string
{
    case Electricity = 'electricity';
    case Water = 'water';
    case Internet = 'internet';
    case Salary = 'salary';
    case Repair = 'repair';
    case Cleaning = 'cleaning';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => 'Listrik',
            self::Water => 'Air',
            self::Internet => 'Wifi / Internet',
            self::Salary => 'Gaji Penjaga',
            self::Repair => 'Perbaikan',
            self::Cleaning => 'Kebersihan',
            self::Other => 'Lain-lain',
        };
    }
}
