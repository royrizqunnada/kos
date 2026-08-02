<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Policies;

use Src\Identity\Domain\Models\User;
use Src\Shared\Infrastructure\Policies\ModulePolicy;

final class UserPolicy extends ModulePolicy
{
    public function __construct()
    {
        parent::__construct('user');
    }

    public function update(User $user, ?User $target = null): bool
    {
        return parent::update($user);
    }

    /** Akun sendiri tidak boleh dihapus dari halaman manajemen user. */
    public function delete(User $user, ?User $target = null): bool
    {
        if ($target !== null && $target->id === $user->id) {
            return false;
        }

        return parent::delete($user);
    }
}
