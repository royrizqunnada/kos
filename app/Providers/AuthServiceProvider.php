<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Src\Expense\Domain\Models\Expense;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;
use Src\Invoice\Domain\Models\Invoice;
use Src\Lease\Domain\Models\Lease;
use Src\Payment\Domain\Models\Payment;
use Src\Room\Domain\Models\Room;
use Src\Room\Infrastructure\Policies\RoomPolicy;
use Src\Shared\Infrastructure\Policies\ModulePolicy;
use Src\Tenant\Domain\Models\Tenant;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super Admin bypasses every gate check.
        Gate::before(function (User $user) {
            return $user->hasRole(UserRole::SuperAdmin->value) ? true : null;
        });

        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Tenant::class, fn () => new ModulePolicy('tenant'));
        Gate::policy(Lease::class, fn () => new ModulePolicy('lease'));
        Gate::policy(Invoice::class, fn () => new ModulePolicy('invoice'));
        Gate::policy(Payment::class, fn () => new ModulePolicy('payment'));
        Gate::policy(Expense::class, fn () => new ModulePolicy('expense'));
    }
}
