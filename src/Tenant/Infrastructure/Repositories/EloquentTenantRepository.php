<?php

declare(strict_types=1);

namespace Src\Tenant\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Tenant\Domain\Models\Tenant;
use Src\Tenant\Domain\Repositories\TenantRepositoryInterface;

final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::query()
            ->withCount(['leases', 'leases as active_count' => fn ($q) => $q->where('status', 'active')])
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where(fn ($w) => $w->where('name', 'ilike', "%$s%")
                    ->orWhere('nik', 'ilike', "%$s%")
                    ->orWhere('phone', 'ilike', "%$s%"));
            })
            ->when(($filters['status'] ?? null) === 'aktif', fn ($q) => $q->whereHas('leases', fn ($l) => $l->where('status', 'active')))
            ->when(($filters['status'] ?? null) === 'mantan', fn ($q) => $q->whereHas('leases')->whereDoesntHave('leases', fn ($l) => $l->where('status', 'active')))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Tenant
    {
        return Tenant::query()->with(['leases.room'])->find($id);
    }

    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->refresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    public function countActive(): int
    {
        return Tenant::query()
            ->whereHas('leases', fn ($q) => $q->where('status', 'active'))
            ->count();
    }
}
