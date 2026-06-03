<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Super Admin', 'superadmin@kos.test', UserRole::SuperAdmin],
            ['Pemilik Kos', 'owner@kos.test', UserRole::Owner],
            ['Admin Operasional', 'admin@kos.test', UserRole::Admin],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'), 'phone' => '08123456789']
            );
            $user->syncRoles($role->value);
        }
    }
}
