<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schedule;
use Src\Expense\Application\Actions\GenerateRecurringExpensesAction;
use Src\Invoice\Application\Actions\GenerateDueInvoicesAction;
use Src\Invoice\Application\Actions\RefreshInvoiceStatusesAction;
use Src\Lease\Application\Actions\EndExpiredLeasesAction;
use Src\Reminder\Application\Actions\SendInvoiceRemindersAction;

// Tandai tagihan lewat tempo jadi "Telat" otomatis (tiap dini hari).
Schedule::call(fn (RefreshInvoiceStatusesAction $action) => $action->execute())
    ->dailyAt('01:00')->name('refresh-invoice-statuses');

// Akhiri kontrak yang masa berlakunya habis & bebaskan kamar (otomatis).
Schedule::call(fn (EndExpiredLeasesAction $action) => $action->execute())
    ->dailyAt('01:10')->name('end-expired-leases');

// Catat otomatis pengeluaran tetap (gaji penjaga, wifi, dll) tiap pagi saat tanggalnya tiba.
Schedule::call(fn (GenerateRecurringExpensesAction $action) => $action->execute(CarbonImmutable::now()))
    ->dailyAt('03:00')->name('generate-recurring-expenses');

// Generate this month's invoices for all active leases on the billing day.
Schedule::call(function (GenerateDueInvoicesAction $action) {
    $action->execute(CarbonImmutable::now());
})->monthlyOn((int) config('kos.billing_day', 1), '02:00')->name('generate-monthly-invoices');

// Dispatch due-date reminders (H-7, H-3, H-1, H+1, H+7) every morning.
Schedule::call(function (SendInvoiceRemindersAction $action) {
    $action->execute(CarbonImmutable::now()->startOfDay());
})->dailyAt('08:00')->name('send-invoice-reminders');
