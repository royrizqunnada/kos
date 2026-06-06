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
     * PENDAPATAN (Revenue Recognition) — alokasi pembayaran per BULAN SEWA, bukan prorata hari.
     *
     * Pembayaran tiap tagihan dibagi rata sesuai JUMLAH BULAN SEWA (durasi), lalu tiap
     * bulan sewa diakui PENUH di bulan kalender tanggal mulainya. Contoh kontrak 3 bulan
     * 20 Mar–19 Jun, sewa Rp1.5jt → Maret 1.5jt, April 1.5jt, Mei 1.5jt (Juni 0).
     * TIDAK ada prorata harian & tidak dibagi jumlah hari/bulan kalender yang tersentuh.
     */
    public function income(CarbonImmutable $from, CarbonImmutable $to): float
    {
        $rangeFromMonth = $from->startOfMonth();
        $rangeToMonth = $to->startOfMonth();

        return (float) Invoice::query()
            ->where('paid_amount', '>', 0)
            ->whereDate('period_start', '<=', $to->toDateString())
            ->whereDate('period_end', '>=', $from->toDateString())
            ->get(['paid_amount', 'period_start', 'period_end'])
            ->sum(function (Invoice $invoice) use ($rangeFromMonth, $rangeToMonth): float {
                $paid = (float) $invoice->paid_amount;
                // toImmutable: cast 'date' Laravel mengembalikan Carbon mutable; tanpa ini
                // addMonths() akan mengubah $start tiap iterasi (hitungan bulan jadi kacau).
                $start = $invoice->period_start->toImmutable();
                $periodEnd = $invoice->period_end->toImmutable();

                // Tiap bulan sewa = tanggal mulai + k bulan (anniversary), diakui di bulan
                // kalender tanggal itu. Kontrak 3 bulan tetap 3 bulan walau lintas 4 kalender.
                $totalMonths = 0;
                $monthsInRange = 0;
                for ($k = 0; $k < 600; $k++) {
                    $anniversary = $start->addMonths($k);
                    if ($anniversary->greaterThan($periodEnd)) {
                        break;
                    }
                    $totalMonths++;
                    $bucket = $anniversary->startOfMonth();
                    if ($bucket->greaterThanOrEqualTo($rangeFromMonth) && $bucket->lessThanOrEqualTo($rangeToMonth)) {
                        $monthsInRange++;
                    }
                }

                return $totalMonths === 0 ? 0.0 : $paid * $monthsInRange / $totalMonths;
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
