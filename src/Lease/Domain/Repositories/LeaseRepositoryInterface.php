<?php

declare(strict_types=1);

namespace Src\Lease\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Lease\Domain\Models\Lease;

interface LeaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Lease;

    public function create(array $data): Lease;

    public function update(Lease $lease, array $data): Lease;

    public function activeForRoom(int $roomId): ?Lease;
}
