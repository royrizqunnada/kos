<?php

declare(strict_types=1);

namespace Src\Reminder\Application\Actions;

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Src\Reminder\Application\Services\ReminderService;

final readonly class SendInvoiceRemindersAction
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices,
        private ReminderService $reminders,
    ) {}

    /** Runs daily: for each configured offset, notify invoices whose due date matches. */
    public function execute(?CarbonImmutable $today = null): int
    {
        $today = $today ?? CarbonImmutable::now()->startOfDay();
        $sent = 0;

        foreach (config('kos.reminder_offsets') as $offset) {
            // offset -7 => 7 days before due; +7 => 7 days after due.
            $targetDue = $today->addDays(-$offset);

            foreach ($this->invoices->dueOn($targetDue->toDateString()) as $invoice) {
                $this->reminders->sendForInvoice($invoice, $offset);
                $sent++;
            }
        }

        return $sent;
    }
}
