<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Src\Identity\Domain\Enums\UserRole;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = ['room', 'tenant', 'lease', 'invoice', 'payment', 'expense', 'report', 'setting', 'user'];
        $abilities = ['viewAny', 'view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($abilities as $ability) {
                Permission::findOrCreate("{$module}.{$ability}");
            }
        }

        $superAdmin = Role::findOrCreate(UserRole::SuperAdmin->value);
        $owner = Role::findOrCreate(UserRole::Owner->value);
        $admin = Role::findOrCreate(UserRole::Admin->value);

        // Super Admin: everything (handled by Gate::before too).
        $superAdmin->syncPermissions(Permission::all());

        // Owner: read-only access to all data + reports (no user management).
        $owner->syncPermissions(
            Permission::query()
                ->where('name', 'not like', 'user.%')
                ->where(fn ($q) => $q->where('name', 'like', '%.viewAny')->orWhere('name', 'like', '%.view'))
                ->get()
        );

        // Admin: full operational management except settings & user management.
        $admin->syncPermissions(
            Permission::query()
                ->where('name', 'not like', 'setting.%')
                ->where('name', 'not like', 'user.%')
                ->get()
        );
    }
}
