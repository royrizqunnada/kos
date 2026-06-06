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

    public function tagihan(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);
        $invoice->load(['lease.tenant', 'lease.room']);

        return Inertia::render('Invoices/Tagihan', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'period_start' => $invoice->period_start->toDateString(),
                'period_end' => $invoice->period_end->toDateString(),
                'due_date' => $invoice->due_date->toDateString(),
                'amount' => (float) $invoice->amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
                'tenant_name' => $invoice->lease?->tenant?->name,
                'tenant_phone' => $invoice->lease?->tenant?->phone,
                'room_number' => $invoice->lease?->room?->room_number,
            ],
            'kos_name' => config('kos.name'),
            'kos_tagline' => config('kos.tagline'),
            'kos_owner' => config('kos.owner'),
        ]);
    }

    public function generate(GenerateDueInvoicesAction $action): RedirectResponse
    {
        $this->authorize('create', Invoice::class);
        $count = $action->execute(CarbonImmutable::now());

        return back()->with('success', "$count tagihan berhasil dibuat.");
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        // Pembayaran ikut diarsipkan lewat event `deleting` di model Invoice.
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Tagihan beserta pembayarannya diarsipkan.');
    }
}
