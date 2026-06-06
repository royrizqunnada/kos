<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EndLeaseRequest;
use App\Http\Requests\StoreLeaseRequest;
use App\Http\Requests\UpdateLeaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Application\Actions\EndLeaseAction;
use Src\Lease\Application\Actions\UpdateLeaseAction;
use Src\Lease\Domain\Data\LeaseCheckoutData;
use Src\Lease\Domain\Data\LeaseData;
use Src\Lease\Domain\Enums\LeaseDuration;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;
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
}
