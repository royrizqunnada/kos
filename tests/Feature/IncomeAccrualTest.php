<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Models\Payment;
use Src\Reporting\Application\Services\ReportService;

it('recognises revenue spread per month of the lease period (deferred revenue)', function () {
    // Sewa 6 bulan (1 Jan – 30 Jun 2026), dibayar lunas Rp6.000.000.
    Invoice::factory()->create([
        'period_start' => '2026-01-01',
        'period_end' => '2026-06-30',
        'amount' => 6_000_000,
        'paid_amount' => 6_000_000,
    ]);

    $reports = app(ReportService::class);

    // Tiap bulan masa sewa = Rp1.000.000 (6jt / 6 bulan).
    foreach ([1, 2, 3, 4, 5, 6] as $month) {
        $rev = $reports->income(
            CarbonImmutable::create(2026, $month, 1),
            CarbonImmutable::create(2026, $month, 1)->endOfMonth(),
        );
        expect($rev)->toBe(1_000_000.0);
    }

    // Total setahun = total dibayar.
    $year = $reports->income(CarbonImmutable::create(2026, 1, 1), CarbonImmutable::create(2026, 12, 31));
    expect($year)->toBe(6_000_000.0);
});

it('separates cash flow (payment date) from revenue (allocation)', function () {
    $invoice = Invoice::factory()->create([
        'period_start' => '2026-01-01',
        'period_end' => '2026-06-30',
        'amount' => 6_000_000,
        'paid_amount' => 6_000_000,
    ]);

    // Seluruh Rp6jt dibayar 1 Januari 2026.
    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 6_000_000,
        'paid_at' => '2026-01-05 10:00:00',
    ]);

    $reports = app(ReportService::class);

    $jan = ['from' => CarbonImmutable::create(2026, 1, 1), 'to' => CarbonImmutable::create(2026, 1, 31)];
    $feb = ['from' => CarbonImmutable::create(2026, 2, 1), 'to' => CarbonImmutable::create(2026, 2, 28)];

    // KAS MASUK: penuh Rp6jt di Januari, Rp0 di Februari.
    expect($reports->paymentIncome($jan['from'], $jan['to']))->toBe(6_000_000.0);
    expect($reports->paymentIncome($feb['from'], $feb['to']))->toBe(0.0);

    // PENDAPATAN: Rp1jt di Januari, dan tetap Rp1jt di Februari (dialokasikan).
    expect($reports->income($jan['from'], $jan['to']))->toBe(1_000_000.0);
    expect($reports->income($feb['from'], $feb['to']))->toBe(1_000_000.0);
});

it('does not recognise revenue for unpaid invoices', function () {
    Invoice::factory()->create([
        'period_start' => '2026-01-01',
        'period_end' => '2026-06-30',
        'amount' => 6_000_000,
        'paid_amount' => 0,
    ]);

    $income = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 1, 1),
        CarbonImmutable::create(2026, 6, 30),
    );

    expect($income)->toBe(0.0);
});
