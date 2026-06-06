<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EndLeaseRequest;
use App\Http\Requests\StoreLeaseRequest;
use App\Http\Requests\UpdateLeaseRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Application\Actions\DeleteLeaseAction;
use Src\Lease\Application\Actions\EndLeaseAction;
use Src\Lease\Application\Actions\RenewLeaseAction;
use Src\Lease\Application\Actions\UpdateLeaseAction;
use Src\Lease\Domain\Data\LeaseCheckoutData;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Enums\RoomStatus;
use Src\Room\Domain\Models\Room;
use Src\Tenant\Domain\Models\Tenant;
use Throwable;

class LeaseController extends Controller
{
    public function __construct(private readonly LeaseRepositoryInterface $leases) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lease::class);

        return Inertia::render('Leases/Index', [
            'leases' => $this->leases->paginate($request->only('search', 'status')),
            'filters' => $request->only('search', 'status'),
            'statuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => $s->label()], LeaseStatus::cases()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Lease::class);

        return Inertia::render('Leases/Create', [
            'rooms' => Room::query()->where('status', RoomStatus::Available->value)
                ->get(['id', 'room_number', 'price']),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name', 'nik']),
            'durations' => array_map(fn ($d) => ['value' => $d->value, 'label' => $d->label(), 'months' => $d->months()], LeaseDuration::cases()),
        ]);
    }

    public function store(StoreLeaseRequest $request, CreateLeaseAction $action): RedirectResponse
    {
        $this->authorize('create', Lease::class);

        try {
            $action->execute(
                LeaseData::fromArray($request->validated()),
                $request->boolean('generate_invoice', true)
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('leases.index')->with('success', 'Kontrak berhasil dibuat.');
    }

    public function edit(Lease $lease): Response
    {
        $this->authorize('update', $lease);

        return Inertia::render('Leases/Edit', [
            'lease' => [
                'id' => $lease->id,
                'room_id' => $lease->room_id,
                'tenant_id' => $lease->tenant_id,
                'start_date' => $lease->start_date->toDateString(),
                'duration' => $lease->duration->value,
                'monthly_price' => (float) $lease->monthly_price,
                'deposit' => (float) $lease->deposit,
                'notes' => $lease->notes,
            ],
            'rooms' => Room::query()
                ->where('status', RoomStatus::Available->value)
                ->orWhere('id', $lease->room_id)
                ->orderBy('room_number')
                ->get(['id', 'room_number', 'price']),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name', 'nik']),
            'durations' => array_map(fn ($d) => ['value' => $d->value, 'label' => $d->label(), 'months' => $d->months()], LeaseDuration::cases()),
        ]);
    }

    public function update(UpdateLeaseRequest $request, Lease $lease, UpdateLeaseAction $action): RedirectResponse
    {
        $this->authorize('update', $lease);

        try {
            $action->execute($lease, LeaseData::fromArray($request->validated()));
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('leases.show', $lease)->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function show(Lease $lease): Response
    {
        $this->authorize('view', $lease);

        $lease = $this->leases->find($lease->id);

        return Inertia::render('Leases/Show', [
            'lease' => $lease,
            'outstanding' => $lease->outstandingTotal(),
        ]);
    }

    public function end(EndLeaseRequest $request, Lease $lease, EndLeaseAction $action): RedirectResponse
    {
        $this->authorize('update', $lease);

        try {
            $action->execute($lease, LeaseCheckoutData::fromArray($request->validated()));
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('leases.index')->with('success', 'Kontrak diakhiri & kamar dikosongkan.');
    }

    public function renew(Lease $lease): Response
    {
        $this->authorize('create', Lease::class);
        $lease->loadMissing('tenant');

        return Inertia::render('Leases/Renew', [
            'old' => [
                'id' => $lease->id,
                'tenant_id' => $lease->tenant_id,
                'tenant_name' => $lease->tenant?->name,
                'room_id' => $lease->room_id,
                'duration' => $lease->duration->value,
                'monthly_price' => (float) $lease->monthly_price,
                'deposit' => (float) $lease->deposit,
            ],
            'default_start' => CarbonImmutable::parse($lease->end_date)->addDay()->toDateString(),
            'rooms' => Room::query()
                ->where('status', RoomStatus::Available->value)
                ->orWhere('id', $lease->room_id)
                ->orderBy('room_number')
                ->get(['id', 'room_number', 'price']),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name', 'nik']),
            'durations' => array_map(fn ($d) => ['value' => $d->value, 'label' => $d->label(), 'months' => $d->months()], LeaseDuration::cases()),
        ]);
    }

    public function storeRenew(StoreLeaseRequest $request, Lease $lease, RenewLeaseAction $action): RedirectResponse
    {
        $this->authorize('create', Lease::class);

        try {
            $new = $action->execute($lease, LeaseData::fromArray($request->validated()), $request->boolean('generate_invoice', true));
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('leases.show', $new)->with('success', 'Kontrak berhasil diperpanjang.');
    }

    public function destroy(Lease $lease, DeleteLeaseAction $action): RedirectResponse
    {
        $this->authorize('delete', $lease);
        $action->execute($lease);

        return redirect()->route('leases.index')->with('success', 'Kontrak diarsipkan & masih bisa dipulihkan.');
    }

    public function archive(): Response
    {
        $this->authorize('viewAny', Lease::class);

        $leases = Lease::onlyTrashed()
            ->with(['tenant:id,name', 'room:id,room_number'])
            ->latest('deleted_at')
            ->get()
            ->map(fn (Lease $lease) => [
                'id' => $lease->id,
                'tenant_name' => $lease->tenant?->name,
                'room_number' => $lease->room?->room_number,
                'start_date' => $lease->start_date->toDateString(),
                'end_date' => $lease->end_date->toDateString(),
                'deleted_at' => $lease->deleted_at?->toDateTimeString(),
            ]);

        return Inertia::render('Leases/Archive', ['leases' => $leases]);
    }

    public function restore(int $lease): RedirectResponse
    {
        $this->authorize('create', Lease::class);

        $model = Lease::onlyTrashed()->findOrFail($lease);

        DB::transaction(function () use ($model) {
            $invoiceIds = Invoice::onlyTrashed()->where('lease_id', $model->id)->pluck('id')->all();
            if ($invoiceIds !== []) {
                Payment::onlyTrashed()->whereIn('invoice_id', $invoiceIds)->restore();
                Invoice::onlyTrashed()->whereIn('id', $invoiceIds)->restore();
            }

            $model->restore();

            // Bila kontrak aktif & kamarnya masih kosong, tempati kembali.
            if ($model->status === LeaseStatus::Active) {
                $model->room()->where('status', RoomStatus::Available->value)
                    ->update(['status' => RoomStatus::Occupied->value]);
            }
        });

        return redirect()->route('leases.archive')->with('success', 'Kontrak dipulihkan dari arsip.');
    }

    public function forceDelete(int $lease): RedirectResponse
    {
        $this->authorize('delete', Lease::class);

        $model = Lease::onlyTrashed()->findOrFail($lease);
        $model->forceDelete();

        return redirect()->route('leases.archive')->with('success', 'Kontrak dihapus permanen.');
    }
}
