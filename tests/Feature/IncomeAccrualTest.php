<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Reporting\Application\Services\ReportService;

it('recognises rent per month from active leases, even when paid upfront', function () {
    // Kontrak 3 bulan (Apr–Jun 2026), sewa Rp 1.000.000/bln.
    Lease::factory()->create([
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-30',
        'monthly_price' => 1_000_000,
        'status' => LeaseStatus::Active,
    ]);

    $reports = app(ReportService::class);

    $april = $reports->income(CarbonImmutable::create(2026, 4, 1), CarbonImmutable::create(2026, 4, 30));
    $may = $reports->income(CarbonImmutable::create(2026, 5, 1), CarbonImmutable::create(2026, 5, 31));
    $june = $reports->income(CarbonImmutable::create(2026, 6, 1), CarbonImmutable::create(2026, 6, 30));
    $quarter = $reports->income(CarbonImmutable::create(2026, 4, 1), CarbonImmutable::create(2026, 6, 30));
    $january = $reports->income(CarbonImmutable::create(2026, 1, 1), CarbonImmutable::create(2026, 1, 31));

    // Tiap bulan masa sewa = harga sewa bulanan (bersih).
    expect($april)->toBe(1_000_000.0)
        ->and($may)->toBe(1_000_000.0)
        ->and($june)->toBe(1_000_000.0);

    // Bulan di luar masa sewa = 0.
    expect($january)->toBe(0.0);

    // Total seluruh masa sewa = sewa × jumlah bulan.
    expect($quarter)->toBe(3_000_000.0);
});

it('sums monthly rent across all active leases for a month', function () {
    foreach ([1_600_000, 1_400_000, 1_500_000, 1_400_000] as $price) {
        Lease::factory()->create([
            'start_date' => '2026-03-01',
            'end_date' => '2026-05-31',
            'monthly_price' => $price,
            'status' => LeaseStatus::Active,
        ]);
    }

    $mei = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 5, 1),
        CarbonImmutable::create(2026, 5, 31),
    );

    // 1.6 + 1.4 + 1.5 + 1.4 juta = 5,9 juta penuh.
    expect($mei)->toBe(5_900_000.0);
});

it('ignores cancelled and pending leases', function () {
    Lease::factory()->create([
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-30',
        'monthly_price' => 1_000_000,
        'status' => LeaseStatus::Cancelled,
    ]);
    Lease::factory()->create([
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-30',
        'monthly_price' => 1_000_000,
        'status' => LeaseStatus::Pending,
    ]);

    $income = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 4, 1),
        CarbonImmutable::create(2026, 6, 30),
    );

    expect($income)->toBe(0.0);
});
