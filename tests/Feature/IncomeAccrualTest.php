<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Domain\Models\Payment;
use Src\Reporting\Application\Services\ReportService;

it('allocates a paid invoice across its rental months (no daily proration)', function () {
    // Tagihan 3 bulan (07 Jun – 06 Sep 2026), dibayar lunas Rp4.800.000 (1,6jt/bln).
    Invoice::factory()->create([
        'period_start' => '2026-06-07',
        'period_end' => '2026-09-06',
        'amount' => 4_800_000,
        'paid_amount' => 4_800_000,
    ]);

    $reports = app(ReportService::class);

    // Tiap bulan sewa diakui Rp1,6jt (Jun, Jul, Agu). September = 0 (bulan sewa habis 06 Sep).
    expect($reports->income(CarbonImmutable::create(2026, 6, 1), CarbonImmutable::create(2026, 6, 30)))->toBe(1_600_000.0);
    expect($reports->income(CarbonImmutable::create(2026, 7, 1), CarbonImmutable::create(2026, 7, 31)))->toBe(1_600_000.0);
    expect($reports->income(CarbonImmutable::create(2026, 8, 1), CarbonImmutable::create(2026, 8, 31)))->toBe(1_600_000.0);
    expect($reports->income(CarbonImmutable::create(2026, 9, 1), CarbonImmutable::create(2026, 9, 30)))->toBe(0.0);

    // Total setahun = total dibayar (tidak ada hitung dobel walau lintas tengah bulan).
    expect($reports->income(CarbonImmutable::create(2026, 1, 1), CarbonImmutable::create(2026, 12, 31)))->toBe(4_800_000.0);
});

it('keeps annual revenue equal to annual cash', function () {
    foreach ([
        ['2026-06-01', '2026-08-31', 4_800_000],
        ['2026-06-02', '2026-09-01', 4_200_000],
        ['2026-06-07', '2026-09-06', 4_800_000],
    ] as [$start, $end, $amount]) {
        $inv = Invoice::factory()->create(['period_start' => $start, 'period_end' => $end, 'amount' => $amount, 'paid_amount' => $amount]);
        Payment::factory()->create(['invoice_id' => $inv->id, 'amount' => $amount, 'paid_at' => $start.' 09:00:00']);
    }

    $reports = app(ReportService::class);
    $from = CarbonImmutable::create(2026, 1, 1);
    $to = CarbonImmutable::create(2026, 12, 31);

    // Pendapatan tahun = Kas masuk tahun (sama-sama 13,8jt).
    expect($reports->income($from, $to))->toBe(13_800_000.0);
    expect($reports->paymentIncome($from, $to))->toBe(13_800_000.0);
});

it('separates cash flow (payment date) from revenue (allocation)', function () {
    $invoice = Invoice::factory()->create([
        'period_start' => '2026-01-01',
        'period_end' => '2026-03-31',
        'amount' => 6_000_000,
        'paid_amount' => 6_000_000,
    ]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 6_000_000, 'paid_at' => '2026-01-05 10:00:00']);

    $reports = app(ReportService::class);
    $jan = ['from' => CarbonImmutable::create(2026, 1, 1), 'to' => CarbonImmutable::create(2026, 1, 31)];
    $feb = ['from' => CarbonImmutable::create(2026, 2, 1), 'to' => CarbonImmutable::create(2026, 2, 28)];

    // KAS MASUK: penuh Rp6jt di Januari, Rp0 di Februari.
    expect($reports->paymentIncome($jan['from'], $jan['to']))->toBe(6_000_000.0);
    expect($reports->paymentIncome($feb['from'], $feb['to']))->toBe(0.0);

    // PENDAPATAN: Rp2jt di Januari, dan tetap Rp2jt di Februari (dialokasikan).
    expect($reports->income($jan['from'], $jan['to']))->toBe(2_000_000.0);
    expect($reports->income($feb['from'], $feb['to']))->toBe(2_000_000.0);
});

it('does not recognise revenue for unpaid invoices', function () {
    Invoice::factory()->create([
        'period_start' => '2026-01-01',
        'period_end' => '2026-03-31',
        'amount' => 6_000_000,
        'paid_amount' => 0,
    ]);

    $income = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 1, 1),
        CarbonImmutable::create(2026, 6, 30),
    );

    expect($income)->toBe(0.0);
});
