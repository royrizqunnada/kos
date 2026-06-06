<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Models\Invoice;
use Src\Reporting\Application\Services\ReportService;

it('spreads a prepaid multi-month invoice across the months it covers', function () {
    // Tagihan 3 bulan (Apr–Jun 2026), lunas Rp 3.000.000.
    Invoice::factory()->create([
        'period_start' => '2026-04-01',
        'period_end' => '2026-06-30',
        'amount' => 3_000_000,
        'paid_amount' => 3_000_000,
    ]);

    $reports = app(ReportService::class);

    $april = $reports->income(CarbonImmutable::create(2026, 4, 1), CarbonImmutable::create(2026, 4, 30));
    $may = $reports->income(CarbonImmutable::create(2026, 5, 1), CarbonImmutable::create(2026, 5, 31));
    $june = $reports->income(CarbonImmutable::create(2026, 6, 1), CarbonImmutable::create(2026, 6, 30));
    $quarter = $reports->income(CarbonImmutable::create(2026, 4, 1), CarbonImmutable::create(2026, 6, 30));
    $january = $reports->income(CarbonImmutable::create(2026, 1, 1), CarbonImmutable::create(2026, 1, 31));

    // Tiap bulan dapat porsi BERSIH 1/3 (Rp 1.000.000), bukan belang per hari.
    expect($april)->toBe(1_000_000.0)
        ->and($may)->toBe(1_000_000.0)
        ->and($june)->toBe(1_000_000.0);

    // Bulan di luar periode = 0.
    expect($january)->toBe(0.0);

    // Total seluruh periode = jumlah dibayar.
    expect($quarter)->toBe(3_000_000.0);

    // Penjumlahan per bulan juga utuh.
    expect($april + $may + $june)->toBe(3_000_000.0);
});

it('ignores unpaid invoices', function () {
    Invoice::factory()->create([
        'period_start' => '2026-04-01',
        'period_end' => '2026-06-30',
        'amount' => 3_000_000,
        'paid_amount' => 0,
    ]);

    $income = app(ReportService::class)->income(
        CarbonImmutable::create(2026, 4, 1),
        CarbonImmutable::create(2026, 6, 30),
    );

    expect($income)->toBe(0.0);
});
