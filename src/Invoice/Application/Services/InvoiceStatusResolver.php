<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Services;

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;

final class InvoiceStatusResolver
{
    public function resolve(Invoice $invoice): InvoiceStatus
    {
        if ($invoice->status === InvoiceStatus::Draft) {
            return InvoiceStatus::Draft;
        }

        $amount = (float) $invoice->amount;
        $paid = (float) $invoice->paid_amount;

        if ($paid >= $amount && $amount > 0) {
            return InvoiceStatus::Paid;
        }

        $isOverdue = CarbonImmutable::parse($invoice->due_date)->isPast();

        if ($paid > 0) {
            return $isOverdue ? InvoiceStatus::Overdue : InvoiceStatus::Partial;
        }

        return $isOverdue ? InvoiceStatus::Overdue : InvoiceStatus::Unpaid;
    }
}
