<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schedule;
use Src\Invoice\Application\Actions\GenerateDueInvoicesAction;
use Src\Reminder\Application\Actions\SendInvoiceRemindersAction;

// Generate this month's invoices for all active leases on the billing day.
Schedule::call(function (GenerateDueInvoicesAction $action) {
    $action->execute(CarbonImmutable::now());
})->monthlyOn((int) config('kos.billing_day', 1), '02:00')->name('generate-monthly-invoices');

// Dispatch due-date reminders (H-7, H-3, H-1, H+1, H+7) every morning.
Schedule::call(function (SendInvoiceRemindersAction $action) {
    $action->execute(CarbonImmutable::now()->startOfDay());
})->dailyAt('08:00')->name('send-invoice-reminders');
