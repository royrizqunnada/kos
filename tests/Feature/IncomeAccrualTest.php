<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
use Src\Reporting\Application\Services\ReportService;

it('recognises full monthly rent for every active month (no daily proration)', function () {
    // Kontrak 20 Mar – 19 Jun 2026, sewa Rp1.5jt/bln.
    Lease::factory()->create([
        'start_date' => '2026-03-20',
        'end_date' => '2026-06-19',
        'monthly_price' => 1_500_000,
        'status' => LeaseStatus::Active,
    ]);

    $reports = app(ReportService::class);

    // Tiap bulan yang masih disentuh masa sewa = penuh Rp1.5jt (Mar, Apr, Mei, Jun).
    foreach ([3, 4, 5, 6] as $month) {
        $rev = $reports->income(
            CarbonImmutable::create(2026, $month, 1),
            CarbonImmutable::create(2026, $month, 1)->endOfMonth(),
        );
        expect($rev)->toBe(1_500_000.0);
    }

    // Bulan di luar masa sewa = 0.
    expect($reports->income(CarbonImmutable::create(2026, 2, 1), CarbonImmutable::create(2026, 2, 28)))->toBe(0.0);
    expect($reports->income(CarbonImmutable::create(2026, 7, 1), CarbonImmutable::create(2026, 7, 31)))->toBe(0.0);
});

it('matches the kos business example: 4 contracts = 5.9jt per active month', function () {
    foreach ([1_500_000, 1_400_000, 1_400_000, 1_600_000] as $monthly) {
        Lease::factory()->create([
            'start_date' => '2026-03-20',
            'end_date' => '2026-06-19',
            'monthly_price' => $monthly,
            'status' => LeaseStatus::Active,
        ]);
    }

    $reports = app(ReportService::class);

    // Pendapatan satu bulan aktif (mis. Juni, kontrak masih jalan) = total sewa = 5.9jt.
    expect($reports->income(CarbonImmutable::create(2026, 6, 1), CarbonImmutable::create(2026, 6, 30)))->toBe(5_900_000.0);

    // Pendapatan rentang 3 bulan (Apr–Jun) = 3 × 5.9jt = 17.7jt.
    expect($reports->income(CarbonImmutable::create(2026, 4, 1), CarbonImmutable::create(2026, 6, 30)))->toBe(17_700_000.0);
});

it('separates cash flow (payment date) from revenue (accrual per active month)', function () {
    // Kontrak 3 bulan, sewa Rp2jt/bln (1 Jan – 31 Mar 2026).
    $lease = Lease::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-03-31',
        'monthly_price' => 2_000_000,
        'status' => LeaseStatus::Active,
    ]);
    $invoice = Invoice::factory()->create([
        'lease_id' => $lease->id,
        'period_start' => '2026-01-01',
        'period_end' => '2026-03-31',
        'amount' => 6_000_000,
        'paid_amount' => 6_000_000,
    ]);
    // Seluruh Rp6jt dibayar 5 Januari.
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

    // PENDAPATAN: Rp2jt di Januari, dan tetap Rp2jt di Februari (akrual per bulan aktif).
    expect($reports->income($jan['from'], $jan['to']))->toBe(2_000_000.0);
    expect($reports->income($feb['from'], $feb['to']))->toBe(2_000_000.0);
});

it('ignores cancelled and pending leases', function () {
    Lease::factory()->create([
        'start_date' => '2026-04-01', 'end_date' => '2026-06-30',
        'monthly_price' => 1_000_000, 'status' => LeaseStatus::Cancelled,
    ]);
    Lease::factory()->create([
        'start_date' => '2026-04-01', 'end_date' => '2026-06-30',
        'monthly_price' => 1_000_000, 'status' => LeaseStatus::Pending,
    ]);

    $income = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 4, 1),
        CarbonImmutable::create(2026, 6, 30),
    );

    expect($income)->toBe(0.0);
});
