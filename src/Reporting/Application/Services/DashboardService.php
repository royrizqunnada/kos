<?php

declare(strict_types=1);

namespace Src\Reporting\Application\Services;

use Carbon\CarbonImmutable;
use Src\Expense\Domain\Models\Expense;
use Src\Invoice\Domain\Models\Invoice;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
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
            'overdue_invoices' => Invoice::query()
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->whereDate('due_date', '<', $now->toDateString())
                ->count(),
            'recent_activity' => $this->recentActivity(),
        ];
    }

    /**
     * Gabungan aktivitas terbaru: pembayaran, kontrak baru, pengeluaran.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentActivity(): array
    {
        $items = collect();

        Payment::query()->with('invoice.lease.tenant')->latest('paid_at')->take(5)->get()
            ->each(fn (Payment $p) => $items->push([
                'type' => 'payment',
                'title' => $p->invoice?->lease?->tenant?->name ?? 'Pembayaran',
                'subtitle' => 'Bayar sewa'.($p->invoice?->lease?->room ? ' — Kamar '.$p->invoice->lease->room->room_number : ''),
                'amount' => (float) $p->amount,
                'date' => $p->paid_at?->toIso8601String(),
            ]));

        Lease::query()->with(['tenant', 'room'])->latest('created_at')->take(5)->get()
            ->each(fn (Lease $l) => $items->push([
                'type' => 'lease',
                'title' => $l->tenant?->name ?? 'Kontrak baru',
                'subtitle' => 'Kontrak baru — Kamar '.($l->room?->room_number ?? '-'),
                'amount' => null,
                'date' => $l->created_at?->toIso8601String(),
            ]));

        Expense::query()->latest('spent_at')->take(5)->get()
            ->each(fn (Expense $e) => $items->push([
                'type' => 'expense',
                'title' => $e->description ?: 'Pengeluaran',
                'subtitle' => 'Pengeluaran bulanan',
                'amount' => -(float) $e->amount,
                'date' => $e->spent_at?->toIso8601String(),
            ]));

        return $items->filter(fn ($i) => $i['date'] !== null)
            ->sortByDesc('date')
            ->take(6)
            ->values()
            ->all();
    }
}
