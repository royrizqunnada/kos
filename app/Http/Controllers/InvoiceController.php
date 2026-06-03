<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Invoice\Application\Actions\GenerateDueInvoicesAction;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceRepositoryInterface $invoices) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        return Inertia::render('Invoices/Index', [
            'invoices' => $this->invoices->paginate($request->only('search', 'status')),
            'filters' => $request->only('search', 'status'),
            'statuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => $s->label()], InvoiceStatus::cases()),
            'outstandingTotal' => (float) $this->invoices->outstandingTotal(),
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', ['invoice' => $this->invoices->find($invoice->id)]);
    }

    public function generate(GenerateDueInvoicesAction $action): RedirectResponse
    {
        $this->authorize('create', Invoice::class);
        $count = $action->execute(CarbonImmutable::now());

        return back()->with('success', "$count tagihan bulan ini berhasil dibuat.");
    }
}
