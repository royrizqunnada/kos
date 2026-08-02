<?php

declare(strict_types=1);

namespace Src\Identity\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Identity\Domain\Models\User;

interface UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data, string $role): User;

    public function update(User $user, array $data, ?string $role = null): User;

    public function updatePassword(User $user, string $password): void;

    public function delete(User $user): void;

    public function countByRole(string $role): int;
}
