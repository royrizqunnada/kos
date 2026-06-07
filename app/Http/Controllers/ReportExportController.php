<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Src\Expense\Domain\Models\Expense;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
use Src\Reporting\Application\Services\ReportService;

class ReportExportController extends Controller
{
    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function range(Request $request): array
    {
        $from = $request->date('from')
            ? CarbonImmutable::parse($request->date('from'))
            : CarbonImmutable::now()->startOfMonth();
        $to = $request->date('to')
            ? CarbonImmutable::parse($request->date('to'))
            : CarbonImmutable::now()->endOfMonth();

        return [$from, $to];
    }

    /** Detail transaksi dalam periode (eager-load relasi: strict mode produksi). */
    private function details(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        return [
            'payments' => Payment::query()
                ->with(['invoice.lease.tenant', 'invoice.lease.room'])
                ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
                ->orderBy('paid_at')->get(),
            'invoices' => Invoice::query()
                ->with(['lease.tenant', 'lease.room'])
                ->whereDate('period_start', '<=', $toStr)
                ->whereDate('period_end', '>=', $fromStr)
                ->orderBy('due_date')->get(),
            'expenses' => Expense::query()
                ->whereBetween('spent_at', [$fromStr, $toStr])
                ->orderBy('spent_at')->get(),
            'leases' => Lease::query()
                ->with(['tenant', 'room'])
                ->whereDate('start_date', '<=', $toStr)
                ->whereDate('end_date', '>=', $fromStr)
                ->orderBy('start_date')->get(),
        ];
    }

    public function csv(Request $request, ReportService $reports)
    {
        $this->authorize('viewAny', Expense::class);
        [$from, $to] = $this->range($request);

        $summary = $reports->summary($from, $to);
        $series = $reports->monthlySeries((int) $to->year);
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        ['payments' => $payments, 'invoices' => $invoices, 'expenses' => $expenses, 'leases' => $leases] = $this->details($from, $to);

        $filename = "laporan-lengkap-{$fromStr}-{$toStr}.csv";

        return response()->streamDownload(function () use ($summary, $series, $payments, $invoices, $expenses, $leases, $fromStr, $toStr) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            $tgl = fn ($d) => $d ? CarbonImmutable::parse($d)->translatedFormat('d M Y') : '-';

            fputcsv($out, ['LAPORAN KEUANGAN '.config('kos.name', 'Cozy Corner')]);
            fputcsv($out, ['Periode', "$fromStr s/d $toStr"]);
            fputcsv($out, []);

            // 1. Ringkasan
            fputcsv($out, ['== RINGKASAN ==']);
            fputcsv($out, ['Pendapatan (Rp)', $summary['income']]);
            fputcsv($out, ['Pengeluaran (Rp)', $summary['expense']]);
            fputcsv($out, ['Laba bersih (Rp)', $summary['profit']]);
            fputcsv($out, ['Piutang / belum dibayar (Rp)', $summary['receivables']]);
            fputcsv($out, ['Total kamar', $summary['occupancy']['total']]);
            fputcsv($out, ['Terisi', $summary['occupancy']['occupied']]);
            fputcsv($out, ['Kosong', $summary['occupancy']['available']]);
            fputcsv($out, ['Tingkat hunian (%)', $summary['occupancy']['rate']]);
            fputcsv($out, ['Penghuni aktif', $summary['active_tenants']]);
            fputcsv($out, []);

            // 2. Pembayaran (kas masuk)
            fputcsv($out, ['== PEMBAYARAN (KAS MASUK) ==']);
            fputcsv($out, ['Tanggal', 'Penghuni', 'Kamar', 'No. Invoice', 'Metode', 'Nominal (Rp)', 'Catatan']);
            $totalPay = 0.0;
            foreach ($payments as $p) {
                $totalPay += (float) $p->amount;
                fputcsv($out, [
                    $tgl($p->paid_at),
                    $p->invoice?->lease?->tenant?->name ?? '-',
                    $p->invoice?->lease?->room?->room_number ?? '-',
                    $p->invoice?->invoice_number ?? '-',
                    $p->method->value,
                    (float) $p->amount,
                    $p->note,
                ]);
            }
            fputcsv($out, ['', '', '', '', 'TOTAL', $totalPay]);
            fputcsv($out, []);

            // 3. Tagihan
            fputcsv($out, ['== TAGIHAN ==']);
            fputcsv($out, ['No. Invoice', 'Penghuni', 'Kamar', 'Periode', 'Jatuh Tempo', 'Total (Rp)', 'Dibayar (Rp)', 'Sisa (Rp)', 'Status']);
            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv->invoice_number,
                    $inv->lease?->tenant?->name ?? '-',
                    $inv->lease?->room?->room_number ?? '-',
                    $tgl($inv->period_start).' - '.$tgl($inv->period_end),
                    $tgl($inv->due_date),
                    (float) $inv->amount,
                    (float) $inv->paid_amount,
                    (float) $inv->amount - (float) $inv->paid_amount,
                    $inv->status->label(),
                ]);
            }
            fputcsv($out, []);

            // 4. Pengeluaran
            fputcsv($out, ['== PENGELUARAN ==']);
            fputcsv($out, ['Tanggal', 'Kategori', 'Deskripsi', 'Nominal (Rp)']);
            $totalExp = 0.0;
            foreach ($expenses as $e) {
                $totalExp += (float) $e->amount;
                fputcsv($out, [$tgl($e->spent_at), $e->category->label(), $e->description, (float) $e->amount]);
            }
            fputcsv($out, ['', '', 'TOTAL', $totalExp]);
            fputcsv($out, []);

            // 5. Kontrak
            fputcsv($out, ['== KONTRAK ==']);
            fputcsv($out, ['Penghuni', 'Kamar', 'Mulai', 'Habis', 'Harga/bln (Rp)', 'Status']);
            foreach ($leases as $l) {
                fputcsv($out, [
                    $l->tenant?->name ?? '-',
                    $l->room?->room_number ?? '-',
                    $tgl($l->start_date),
                    $tgl($l->end_date),
                    (float) $l->monthly_price,
                    $l->status->label(),
                ]);
            }
            fputcsv($out, []);

            // 6. Tren bulanan
            fputcsv($out, ['== TREN BULANAN ==']);
            fputcsv($out, ['Bulan', 'Pendapatan (Rp)', 'Pengeluaran (Rp)']);
            foreach ($series as $s) {
                fputcsv($out, [$s['month'], $s['income'], $s['expense']]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function print(Request $request, ReportService $reports)
    {
        $this->authorize('viewAny', Expense::class);
        [$from, $to] = $this->range($request);

        return view('reports.print', [
            'summary' => $reports->summary($from, $to),
            'series' => $reports->monthlySeries((int) $to->year),
            'from' => $from,
            'to' => $to,
            'appName' => config('kos.name', 'Cozy Corner'),
            'kos' => config('kos'),
            ...$this->details($from, $to),
        ]);
    }
}
