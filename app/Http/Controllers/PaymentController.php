<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Invoice\Domain\Enums\InvoiceStatus;
use Src\Invoice\Domain\Models\Invoice;
use Src\Payment\Application\Actions\DeletePaymentAction;
use Src\Payment\Application\Actions\RecordPaymentAction;
use Src\Payment\Application\Actions\UpdatePaymentAction;
use Src\Payment\Application\Services\PaymentService;
use Src\Payment\Domain\Data\PaymentData;
use Src\Payment\Domain\Models\Payment;
use Throwable;

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

    public function edit(Payment $payment): Response
    {
        $this->authorize('update', $payment);
        $payment->load('invoice.lease.tenant');

        return Inertia::render('Payments/Edit', [
            'payment' => [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice?->invoice_number,
                'tenant' => $payment->invoice?->lease?->tenant?->name,
                'amount' => (float) $payment->amount,
                'method' => $payment->method->value,
                'paid_at' => $payment->paid_at->toDateString(),
                'note' => $payment->note,
                'proof_path' => $payment->proof_path,
            ],
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment, UpdatePaymentAction $action): RedirectResponse
    {
        $this->authorize('update', $payment);

        $fields = [
            'amount' => $request->validated('amount'),
            'method' => $request->validated('method'),
            'paid_at' => $request->validated('paid_at'),
            'note' => $request->validated('note'),
        ];

        if ($request->hasFile('proof')) {
            $fields['proof_path'] = $request->file('proof')->store('proofs', 'public');
        }

        try {
            $action->execute($payment, $fields);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function kwitansi(Payment $payment): Response
    {
        $this->authorize('view', $payment);
        $payment->load(['invoice.lease.tenant', 'invoice.lease.room', 'invoice.items', 'recordedBy']);

        return Inertia::render('Payments/Kwitansi', [
            'payment' => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method->value,
                'paid_at' => $payment->paid_at->toDateString(),
                'note' => $payment->note,
                'recorded_by' => $payment->recordedBy?->name,
                'invoice' => [
                    'invoice_number' => $payment->invoice?->invoice_number,
                    'period_start' => $payment->invoice?->period_start?->toDateString(),
                    'period_end' => $payment->invoice?->period_end?->toDateString(),
                    'amount' => (float) $payment->invoice?->amount,
                ],
                'tenant_name' => $payment->invoice?->lease?->tenant?->name,
                'tenant_phone' => $payment->invoice?->lease?->tenant?->phone,
                'room_number' => $payment->invoice?->lease?->room?->room_number,
            ],
            'kos_name' => config('kos.name'),
            'kos_owner' => config('kos.owner'),
        ]);
    }

    public function destroy(Payment $payment, DeletePaymentAction $action): RedirectResponse
    {
        $this->authorize('delete', $payment);
        $action->execute($payment);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}
