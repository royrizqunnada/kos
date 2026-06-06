<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Tenant\Domain\Models\Tenant;
use Src\Tenant\Domain\Repositories\TenantRepositoryInterface;

class TenantController extends Controller
{
    public function __construct(private readonly TenantRepositoryInterface $tenants) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tenant::class);

        return Inertia::render('Tenants/Index', [
            'tenants' => $this->tenants->paginate($request->only('search', 'status')),
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Tenant::class);

        return Inertia::render('Tenants/Create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);
        $data = $request->safe()->except('ktp_photo');

        if ($request->hasFile('ktp_photo')) {
            $data['ktp_photo'] = $request->file('ktp_photo')->store('ktp', 'public');
        }

        $this->tenants->create($data);

        return redirect()->route('tenants.index')->with('success', 'Penghuni berhasil ditambahkan.');
    }

    public function show(Tenant $tenant): Response
    {
        $this->authorize('view', $tenant);

        $detail = $this->tenants->find($tenant->id);
        $totalPaid = (float) \Src\Payment\Domain\Models\Payment::query()
            ->whereHas('invoice.lease', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->sum('amount');

        return Inertia::render('Tenants/Show', [
            'tenant' => $detail,
            'stats' => [
                'total_leases' => $detail?->leases->count() ?? 0,
                'is_active' => (bool) ($detail?->leases->contains(fn ($l) => $l->status->value === 'active')),
                'total_paid' => $totalPaid,
            ],
        ]);
    }

    public function edit(Tenant $tenant): Response
    {
        $this->authorize('update', $tenant);

        return Inertia::render('Tenants/Edit', ['tenant' => $tenant]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);
        $data = $request->safe()->except('ktp_photo');

        if ($request->hasFile('ktp_photo')) {
            $data['ktp_photo'] = $request->file('ktp_photo')->store('ktp', 'public');
        }

        $this->tenants->update($tenant, $data);

        return redirect()->route('tenants.index')->with('success', 'Penghuni berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);
        $this->tenants->delete($tenant);

        return redirect()->route('tenants.index')->with('success', 'Penghuni berhasil dihapus.');
    }
}
