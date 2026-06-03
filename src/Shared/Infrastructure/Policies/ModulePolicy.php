<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Policies;

use Src\Identity\Domain\Models\User;

class ModulePolicy
{
    public function __construct(protected string $module) {}

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->module}.viewAny");
    }

    public function view(User $user): bool
    {
        return $user->can("{$this->module}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->module}.create");
    }

    public function update(User $user): bool
    {
        return $user->can("{$this->module}.update");
    }

    public function delete(User $user): bool
    {
        return $user->can("{$this->module}.delete");
    }
}
