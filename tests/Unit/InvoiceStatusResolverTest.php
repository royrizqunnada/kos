<?php

declare(strict_types=1);

use Src\Invoice\Application\Services\InvoiceStatusResolver;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;

it('marks invoice as paid when fully paid', function () {
    $invoice = new Invoice(['amount' => 1000, 'paid_amount' => 1000, 'due_date' => now()->addDays(3), 'status' => InvoiceStatus::Unpaid]);

    expect((new InvoiceStatusResolver)->resolve($invoice))->toBe(InvoiceStatus::Paid);
});

it('marks invoice as partial when partly paid before due', function () {
    $invoice = new Invoice(['amount' => 1000, 'paid_amount' => 400, 'due_date' => now()->addDays(3), 'status' => InvoiceStatus::Unpaid]);

    expect((new InvoiceStatusResolver)->resolve($invoice))->toBe(InvoiceStatus::Partial);
});

it('marks invoice as overdue when unpaid and past due', function () {
    $invoice = new Invoice(['amount' => 1000, 'paid_amount' => 0, 'due_date' => now()->subDay(), 'status' => InvoiceStatus::Unpaid]);

    expect((new InvoiceStatusResolver)->resolve($invoice))->toBe(InvoiceStatus::Overdue);
});
