<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Application\Services\PaymentService;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentService $service): Response
    {
        $this->authorize('viewAny', Payment::class);

        return Inertia::render('Payments/Index', [
            'payments' => $service->list($request->only('method')),
            'filters' => $request->only('method'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Payment::class);

        return Inertia::render('Payments/Create', [
            'invoices' => Invoice::query()
                ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
                ->with('lease.tenant')
                ->get()
                ->map(fn (Invoice $i) => [
                    'id' => $i->id,
                    'invoice_number' => $i->invoice_number,
                    'tenant' => $i->lease->tenant->name,
                    'outstanding' => (float) $i->outstanding(),
                ]),
            'preselect' => $request->integer('invoice_id') ?: null,
        ]);
    }

    public function store(StorePaymentRequest $request, RecordPaymentAction $action): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        if ($request->hasFile('proof')) {
            $data['proof_path'] = $request->file('proof')->store('proofs', 'public');
        }

        $action->execute(PaymentData::fromArray($data));

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dicatat.');
    }
}
