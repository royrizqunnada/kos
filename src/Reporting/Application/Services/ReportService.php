<?php

declare(strict_types=1);

namespace Src\Reporting\Application\Services;

use Carbon\CarbonImmutable;
use Src\Expense\Domain\Models\Expense;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;

final class ReportService
{
    /**
     * PENDAPATAN (Revenue Recognition) — harga sewa bulanan tiap kontrak yang AKTIF di bulan itu.
     *
     * Tiap bulan kalender yang masih disentuh masa sewa kontrak diakui PENUH sebesar harga
     * sewa bulanan. Tanpa prorata harian, tanpa dibagi jumlah hari. Kontrak 20 Mar–19 Jun
     * (Rp1.5jt) tetap diakui Rp1.5jt selama Mar, Apr, Mei, Jun (selama penghuni masih ngekos).
     * Pengakuan ini lepas dari pembayaran (akrual) — kas masuk dihitung terpisah di paymentIncome().
     */
    public function income(CarbonImmutable $from, CarbonImmutable $to): float
    {
        $rangeFromMonth = $from->startOfMonth();
        $rangeToMonth = $to->startOfMonth();

        return (float) Lease::query()
            ->whereIn('status', [LeaseStatus::Active->value, LeaseStatus::Ended->value])
            ->whereDate('start_date', '<=', $to->toDateString())
            ->whereDate('end_date', '>=', $from->toDateString())
            ->get(['monthly_price', 'start_date', 'end_date'])
            ->sum(function (Lease $lease) use ($rangeFromMonth, $rangeToMonth): float {
                $monthly = (float) $lease->monthly_price;
                // toImmutable agar addMonth() tidak mengubah variabel aslinya tiap iterasi.
                $monthCursor = $lease->start_date->toImmutable()->startOfMonth();
                $lastMonth = $lease->end_date->toImmutable()->startOfMonth();

                $months = 0;
                for ($m = $monthCursor; $m->lessThanOrEqualTo($lastMonth); $m = $m->addMonth()) {
                    if ($m->greaterThanOrEqualTo($rangeFromMonth) && $m->lessThanOrEqualTo($rangeToMonth)) {
                        $months++;
                    }
                }

                return $monthly * $months;
            });
    }

    /**
     * KAS MASUK (Cash Flow) — total pembayaran berdasarkan TANGGAL bayar.
     * Bayar 6 bulan di muka tercatat penuh di bulan pembayaran.
     */
    public function paymentIncome(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) Payment::query()
            ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->sum('amount');
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
