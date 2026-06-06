<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Src\Invoice\Application\Services\InvoiceNumberGenerator;
use Src\Invoice\Domain\Models\Invoice;

it('does not reuse an invoice number that belongs to an archived invoice', function () {
    // Tagihan pertama lalu diarsipkan (soft-delete).
    $first = Invoice::factory()->create([
        'invoice_number' => 'INV/202603/0001',
        'period_start' => '2026-03-01',
        'period_end' => '2026-05-31',
    ]);
    $first->delete();

    $next = app(InvoiceNumberGenerator::class)->generate(CarbonImmutable::create(2026, 3, 1));

    // Harus lanjut ke 0002, bukan mengulang 0001 (yang bikin bentrok unique constraint).
    expect($next)->toBe('INV/202603/0002');
});
