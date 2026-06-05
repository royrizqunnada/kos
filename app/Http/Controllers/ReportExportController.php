<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Src\Expense\Domain\Models\Expense;
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

    public function csv(Request $request, ReportService $reports)
    {
        $this->authorize('viewAny', Expense::class);
        [$from, $to] = $this->range($request);
        $summary = $reports->summary($from, $to);
        $series = $reports->monthlySeries((int) $to->year);
        $filename = "laporan-{$from->toDateString()}-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($summary, $series, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8

            fputcsv($out, ['Laporan Keuangan', "{$from->toDateString()} s/d {$to->toDateString()}"]);
            fputcsv($out, []);
            fputcsv($out, ['Ringkasan', 'Nilai (Rp)']);
            fputcsv($out, ['Pendapatan', $summary['income']]);
            fputcsv($out, ['Pengeluaran', $summary['expense']]);
            fputcsv($out, ['Laba bersih', $summary['profit']]);
            fputcsv($out, ['Piutang', $summary['receivables']]);
            fputcsv($out, []);
            fputcsv($out, ['Hunian', '']);
            fputcsv($out, ['Total kamar', $summary['occupancy']['total']]);
            fputcsv($out, ['Terisi', $summary['occupancy']['occupied']]);
            fputcsv($out, ['Kosong', $summary['occupancy']['available']]);
            fputcsv($out, ['Tingkat hunian (%)', $summary['occupancy']['rate']]);
            fputcsv($out, ['Penghuni aktif', $summary['active_tenants']]);
            fputcsv($out, []);
            fputcsv($out, ['Bulan', 'Pendapatan', 'Pengeluaran']);
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
            'appName' => config('app.name', 'Cozy Corner'),
        ]);
    }
}
