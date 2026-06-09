<?php

declare(strict_types=1);

use Src\Gallery\Domain\Models\Gallery;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

function galleryAdmin(): User
{
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    return $user;
}

it('lets an admin open the gallery manager', function () {
    actingAs(galleryAdmin())->get('/galleries')->assertOk();
});

it('blocks gallery upload for users without permission', function () {
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Owner->value); // read-only role

    actingAs($owner)->get('/galleries')->assertForbidden();
});

it('shows uploaded photos in the public profile gallery', function () {
    Gallery::query()->create(['category' => 'kos', 'caption' => 'Kamar Tipe A', 'path' => 'galeri/contoh.jpg']);

    $this->get('http://'.config('kos.profile_domain').'/')
        ->assertOk()
        ->assertSee('id="galeri"', false)
        ->assertSee('Kamar Tipe A')
        ->assertSee('galeri/contoh.jpg');
});
