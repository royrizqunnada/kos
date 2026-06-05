<?php

declare(strict_types=1);

namespace Src\Reporting\Application\Services;

use Carbon\CarbonImmutable;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Src\Tenant\Domain\Repositories\TenantRepositoryInterface;

final readonly class DashboardService
{
    public function __construct(
        private ReportService $reports,
        private InvoiceRepositoryInterface $invoices,
        private TenantRepositoryInterface $tenants,
    ) {}

    /** @return array<string, mixed> */
    public function stats(): array
    {
        $now = CarbonImmutable::now();
        $occupancy = $this->reports->occupancy();

        return [
            'rooms_total' => $occupancy['total'],
            'rooms_occupied' => $occupancy['occupied'],
            'rooms_available' => $occupancy['available'],
            'rooms_maintenance' => $occupancy['maintenance'],
            'occupancy_rate' => $occupancy['rate'],
            'active_tenants' => $this->tenants->countActive(),
            'unpaid_invoices' => $this->invoices->unpaidCount(),
            'receivables' => (float) $this->invoices->outstandingTotal(),
            'income_this_month' => $this->reports->income($now->startOfMonth(), $now->endOfMonth()),
            'income_this_year' => $this->reports->income($now->startOfYear(), $now->endOfYear()),
            'expense_this_month' => $this->reports->expense($now->startOfMonth(), $now->endOfMonth()),
        ];
    }
}
