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
     * PENDAPATAN (Revenue Recognition) — alokasi pembayaran per BULAN SEWA.
     *
     * Nilai yang sudah dibayar pada tiap tagihan dibagi rata sesuai JUMLAH BULAN SEWA
     * (durasi), lalu tiap bulan sewa diakui di bulan kalender tanggal mulainya. Bayar 3 bulan
     * Rp4,5jt → diakui Rp1,5jt/bulan selama 3 bulan. Total pendapatan = total kas (cuma
     * sebarannya per bulan), jadi tidak ada hitung dobel / kelebihan bulan tengah.
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
                $start = $invoice->period_start->toImmutable();
                $periodEnd = $invoice->period_end->toImmutable();

                // Hitung jumlah bulan sewa (anniversary) & berapa yang masuk rentang.
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

    /**
     * Rincian KAS MASUK — setiap pembayaran (uang masuk) pada periode, lengkap dengan catatan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function payments(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Payment::query()
            ->with(['invoice.lease.tenant:id,name', 'invoice.lease.room:id,room_number', 'invoice:id,invoice_number,lease_id'])
            ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('paid_at')
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'paid_at' => $p->paid_at?->toIso8601String(),
                'tenant_name' => $p->invoice?->lease?->tenant?->name,
                'room_number' => $p->invoice?->lease?->room?->room_number,
                'invoice_number' => $p->invoice?->invoice_number,
                'method' => $p->method->value,
                'amount' => (float) $p->amount,
                'note' => $p->note,
            ])
            ->all();
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
        $cash = $this->paymentIncome($from, $to);
        $expense = $this->expense($from, $to);

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'income' => $income,
            // Kas masuk = uang yang benar-benar diterima pada periode (basis kas, sama dgn Dashboard)
            'cash' => $cash,
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
