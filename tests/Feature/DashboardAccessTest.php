<?php

declare(strict_types=1);

use Src\Identity\Domain\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

it('redirects guests to login', function () {
    get('/')->assertRedirect('/login');
});

it('allows authenticated users to see the dashboard', function () {
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    actingAs($user)->get('/')->assertOk();
});
