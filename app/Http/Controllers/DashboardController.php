<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Src\Reporting\Application\Services\DashboardService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $dashboard->stats(),
            // Deferred: heavier chart query loaded after first paint.
            'revenueSeries' => Inertia::defer(fn () => app(\Src\Reporting\Application\Services\ReportService::class)
                ->monthlySeries((int) now()->year)),
        ]);
    }
}
