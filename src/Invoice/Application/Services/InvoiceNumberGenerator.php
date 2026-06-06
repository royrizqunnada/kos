<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Services;

use Carbon\CarbonInterface;
use Src\Invoice\Domain\Models\Invoice;

final class InvoiceNumberGenerator
{
    public function generate(CarbonInterface $date): string
    {
        $prefix = 'INV/'.$date->format('Ym').'/';

        // Sertakan tagihan terarsip (soft-deleted): nomornya masih dipakai unique
        // constraint di DB, jadi tetap harus dihitung agar tidak bentrok.
        $last = Invoice::withTrashed()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
