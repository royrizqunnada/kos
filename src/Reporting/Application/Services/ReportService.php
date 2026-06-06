<?php

declare(strict_types=1);

namespace Src\Reporting\Application\Services;

use Carbon\CarbonImmutable;
use Src\Expense\Domain\Models\Expense;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

final class ReportService
{
    /**
     * Pendapatan sesuai masa sewa (accrual): uang yang sudah dibayar pada tiap tagihan
     * disebar rata ke periode yang dicover tagihan itu. Bayar 3 bulan di muka otomatis
     * terbagi ke 3 bulan, jadi angka "bulan ini" tetap kebaca walau bayarnya sekaligus.
     */
    public function income(CarbonImmutable $from, CarbonImmutable $to): float
    {
        $rangeStart = $from->startOfDay();
        $rangeEnd = $to->startOfDay();

        return (float) Invoice::query()
            ->where('paid_amount', '>', 0)
            ->whereDate('period_start', '<=', $rangeEnd->toDateString())
            ->whereDate('period_end', '>=', $rangeStart->toDateString())
            ->get(['paid_amount', 'period_start', 'period_end'])
            ->sum(function (Invoice $invoice) use ($rangeStart, $rangeEnd): float {
                $paid = (float) $invoice->paid_amount;
                $start = $invoice->period_start->startOfDay();
                $end = $invoice->period_end->startOfDay();

                // Jumlah hari periode tagihan (inklusif). Aman bila periode 1 hari.
                $totalDays = (int) $start->diffInDays($end) + 1;
                if ($totalDays <= 1) {
                    return $paid;
                }

                $overlapStart = $start->greaterThan($rangeStart) ? $start : $rangeStart;
                $overlapEnd = $end->lessThan($rangeEnd) ? $end : $rangeEnd;
                $overlapDays = (int) $overlapStart->diffInDays($overlapEnd) + 1;

                return $paid * max(0, $overlapDays) / $totalDays;
            });
    }

    public function expense(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) Expense::query()
            ->whereBetween('spent_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    public function receivables(): float
    {
        return (float) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
            ->selectRaw('coalesce(sum(amount - paid_amount), 0) as total')
            ->value('total');
    }

    /** @return array<string, mixed> */
    public function summary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $income = $this->income($from, $to);
        $expense = $this->expense($from, $to);

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'income' => $income,
            'expense' => $expense,
            'profit' => $income - $expense,
            'receivables' => $this->receivables(),
            'occupancy' => $this->occupancy(),
            'active_tenants' => Lease::query()->where('status', LeaseStatus::Active->value)->distinct('tenant_id')->count('tenant_id'),
        ];
    }

    /** @return array{total:int, occupied:int, available:int, maintenance:int, rate:float} */
    public function occupancy(): array
    {
        $byStatus = Room::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $byStatus->sum();
        $occupied = (int) ($byStatus[RoomStatus::Occupied->value] ?? 0);

        return [
            'total' => $total,
            'occupied' => $occupied,
            'available' => (int) ($byStatus[RoomStatus::Available->value] ?? 0),
            'maintenance' => (int) ($byStatus[RoomStatus::Maintenance->value] ?? 0),
            'rate' => $total > 0 ? round($occupied / $total * 100, 1) : 0.0,
        ];
    }

    /** Monthly income vs expense series for a given year. */
    public function monthlySeries(int $year): array
    {
        $series = [];
        for ($month = 1; $month <= 12; $month++) {
            $from = CarbonImmutable::create($year, $month, 1);
            $to = $from->endOfMonth();
            $series[] = [
                'month' => $from->translatedFormat('M'),
                'income' => $this->income($from, $to),
                'expense' => $this->expense($from, $to),
            ];
        }

        return $series;
    }
}
