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

it('shows facility photos in the profile gallery and kos photos in the carousel', function () {
    Gallery::query()->create(['category' => 'fasilitas', 'caption' => 'Dapur Bersama', 'path' => 'galeri/dapur.jpg']);
    Gallery::query()->create(['category' => 'kos', 'caption' => 'Suasana Kos', 'path' => 'galeri/kos.jpg']);

    $res = $this->get('http://'.config('kos.profile_domain').'/')->assertOk();

    // Foto fasilitas tampil di galeri.
    $res->assertSee('id="galeri"', false)->assertSee('Dapur Bersama')->assertSee('galeri/dapur.jpg');
    // Foto kos tampil di carousel (slide photo), bukan di galeri fasilitas.
    $res->assertSee('slide photo', false)->assertSee('galeri/kos.jpg');
});

it('does not show the gallery section when there are only kos photos', function () {
    Gallery::query()->create(['category' => 'kos', 'caption' => 'Suasana Kos', 'path' => 'galeri/kos.jpg']);

    $this->get('http://'.config('kos.profile_domain').'/')
        ->assertOk()
        ->assertDontSee('id="galeri"', false);
});
