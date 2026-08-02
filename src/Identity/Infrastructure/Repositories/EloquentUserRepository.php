<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Identity\Domain\Models\User;
use Src\Identity\Domain\Repositories\UserRepositoryInterface;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($filters['search'] ?? null, function ($q, $s) {
                $q->where(fn ($w) => $w->where('name', 'ilike', "%$s%")
                    ->orWhere('email', 'ilike', "%$s%")
                    ->orWhere('phone', 'ilike', "%$s%"));
            })
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, string $role): User
    {
        $user = User::query()->create($data);
        $user->syncRoles($role);

        return $user->refresh();
    }

    public function update(User $user, array $data, ?string $role = null): User
    {
        $user->update($data);

        if ($role !== null) {
            $user->syncRoles($role);
        }

        return $user->refresh();
    }

    public function updatePassword(User $user, string $password): void
    {
        // Cast 'hashed' pada model yang melakukan hashing.
        $user->update(['password' => $password]);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function countByRole(string $role): int
    {
        return User::query()->whereHas('roles', fn ($q) => $q->where('name', $role))->count();
    }
}
