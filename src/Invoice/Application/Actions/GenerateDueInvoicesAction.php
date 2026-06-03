<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Actions;

use Carbon\CarbonImmutable;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;

final readonly class GenerateDueInvoicesAction
{
    public function __construct(private GenerateInvoiceAction $generateInvoice) {}

    /** Generate next-month invoices for every active lease. Returns count created. */
    public function execute(?CarbonImmutable $forMonth = null): int
    {
        $periodStart = ($forMonth ?? CarbonImmutable::now())->startOfMonth();
        $created = 0;

        Lease::query()
            ->where('status', LeaseStatus::Active->value)
            ->where('start_date', '<=', $periodStart->endOfMonth())
            ->where('end_date', '>=', $periodStart)
            ->with('room')
            ->chunkById(100, function ($leases) use ($periodStart, &$created) {
                foreach ($leases as $lease) {
                    if ($this->generateInvoice->execute($lease, $periodStart)) {
                        $created++;
                    }
                }
            });

        return $created;
    }
}
