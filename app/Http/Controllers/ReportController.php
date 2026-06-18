<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Reporting\Application\Services\ReportService;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): Response
    {
        $this->authorize('viewAny', \Src\Expense\Domain\Models\Expense::class);

        $from = $request->date('from')
            ? CarbonImmutable::parse($request->date('from'))
            : CarbonImmutable::now()->startOfMonth();
        $to = $request->date('to')
            ? CarbonImmutable::parse($request->date('to'))
            : CarbonImmutable::now()->endOfMonth();

        return Inertia::render('Reports/Index', [
            'summary' => $reports->summary($from, $to),
            'filters' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'payments' => Inertia::defer(fn () => $reports->payments($from, $to)),
            'series' => Inertia::defer(fn () => $reports->monthlySeries((int) $to->year)),
        ]);
    }
}
