<?php

declare(strict_types=1);

namespace Src\Lease\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Lease\Domain\Enums\LeaseStatus;
use Src\Lease\Domain\Models\Lease;
use Src\Lease\Domain\Repositories\LeaseRepositoryInterface;

final class EloquentLeaseRepository implements LeaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Lease::query()
            ->with(['room', 'tenant'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('tenant', fn ($t) => $t->where('name', 'ilike', "%$s%")))
            ->latest('start_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Lease
    {
        return Lease::query()->with(['room', 'tenant', 'invoices'])->find($id);
    }

    public function create(array $data): Lease
    {
        return Lease::query()->create($data);
    }

    public function update(Lease $lease, array $data): Lease
    {
        $lease->update($data);

        return $lease->refresh();
    }

    public function activeForRoom(int $roomId): ?Lease
    {
        return Lease::query()
            ->where('room_id', $roomId)
            ->where('status', LeaseStatus::Active->value)
            ->latest('start_date')
            ->first();
    }
}
