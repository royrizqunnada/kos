<?php

declare(strict_types=1);

namespace Src\Invoice\Application\Actions;

use Carbon\CarbonImmutable;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;

final readonly class GenerateDueInvoicesAction
{
    public function __construct(private GenerateInvoiceAction $generateInvoice) {}

    /**
     * Generate tagihan termin berikutnya untuk tiap kontrak aktif (mengikuti durasi 1/3/6/12 bulan).
     * Tagihan dibuat saat periode berikutnya mendekat (config kos.generate_days_ahead).
     */
    public function execute(?CarbonImmutable $asOf = null): int
    {
        $asOf = ($asOf ?? CarbonImmutable::now())->startOfDay();
        $aheadLimit = $asOf->addDays((int) config('kos.generate_days_ahead', 7));
        $created = 0;

        Lease::query()
            ->where('status', LeaseStatus::Active->value)
            ->with('room')
            ->chunkById(100, function ($leases) use ($aheadLimit, &$created) {
                foreach ($leases as $lease) {
                    $months = $lease->duration->months();
                    $leaseEnd = CarbonImmutable::parse($lease->end_date);

                    $last = $lease->invoices()->orderByDesc('period_end')->first();
                    $nextStart = $last
                        ? CarbonImmutable::parse($last->period_end)->addDay()
                        : CarbonImmutable::parse($lease->start_date)->startOfMonth();

                    // Lewat masa kontrak, atau periode berikutnya masih jauh -> lewati.
                    if ($nextStart->isAfter($leaseEnd) || $nextStart->isAfter($aheadLimit)) {
                        continue;
                    }

                    if ($this->generateInvoice->execute($lease, $nextStart, $months)) {
                        $created++;
                    }
                }
            });

        return $created;
    }
}
