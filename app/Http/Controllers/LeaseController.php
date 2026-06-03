<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Lease\Application\Actions\CreateLeaseAction;
use Src\Lease\Application\Actions\EndLeaseAction;
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

    public function show(Lease $lease): Response
    {
        $this->authorize('view', $lease);

        return Inertia::render('Leases/Show', ['lease' => $this->leases->find($lease->id)]);
    }

    public function end(Lease $lease, EndLeaseAction $action): RedirectResponse
    {
        $this->authorize('update', $lease);
        $action->execute($lease);

        return redirect()->route('leases.index')->with('success', 'Kontrak diakhiri & kamar dikosongkan.');
    }
}
