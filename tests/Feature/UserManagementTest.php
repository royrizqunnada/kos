<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

function userWithRole(UserRole $role): User
{
    seed(\Database\Seeders\RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

it('renders the user management pages for a super admin', function () {
    $admin = userWithRole(UserRole::SuperAdmin);

    actingAs($admin)->get('/users')->assertOk();
    actingAs($admin)->get('/users/create')->assertOk();
    actingAs($admin)->get("/users/{$admin->id}/edit")->assertOk();
});

it('lets a super admin add a new user that can log in', function () {
    $admin = userWithRole(UserRole::SuperAdmin);

    actingAs($admin)->post('/users', [
        'name' => 'Admin Baru',
        'email' => 'baru@kos.test',
        'phone' => '08123456789',
        'role' => UserRole::Admin->value,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertRedirect(route('users.index'));

    $baru = User::query()->where('email', 'baru@kos.test')->first();

    expect($baru)->not->toBeNull()
        ->and($baru->hasRole(UserRole::Admin->value))->toBeTrue()
        ->and(Hash::check('rahasia123', $baru->password))->toBeTrue();
});

it('updates a user email and role', function () {
    $admin = userWithRole(UserRole::SuperAdmin);
    $target = User::factory()->create(['email' => 'lama@kos.test']);
    $target->assignRole(UserRole::Admin->value);

    actingAs($admin)->put("/users/{$target->id}", [
        'name' => 'Nama Baru',
        'email' => 'baru@kos.test',
        'phone' => '0811111111',
        'role' => UserRole::Owner->value,
    ])->assertRedirect(route('users.index'));

    $target->refresh();

    expect($target->email)->toBe('baru@kos.test')
        ->and($target->name)->toBe('Nama Baru')
        ->and($target->hasRole(UserRole::Owner->value))->toBeTrue();
});

it('resets another user password without knowing the old one', function () {
    $admin = userWithRole(UserRole::SuperAdmin);
    $target = User::factory()->create();
    $target->assignRole(UserRole::Admin->value);

    actingAs($admin)->put("/users/{$target->id}/password", [
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ])->assertRedirect();

    expect(Hash::check('passwordbaru', $target->refresh()->password))->toBeTrue();
});

it('rejects a duplicate email', function () {
    $admin = userWithRole(UserRole::SuperAdmin);
    User::factory()->create(['email' => 'dipakai@kos.test']);

    actingAs($admin)->post('/users', [
        'name' => 'Duplikat',
        'email' => 'dipakai@kos.test',
        'role' => UserRole::Admin->value,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasErrors('email');
});

it('refuses to delete your own account', function () {
    $admin = userWithRole(UserRole::SuperAdmin);

    actingAs($admin)->delete("/users/{$admin->id}")->assertSessionHas('error');

    expect(User::find($admin->id))->not->toBeNull();
});

it('keeps at least one super admin', function () {
    $admin = userWithRole(UserRole::SuperAdmin);
    $lain = User::factory()->create();
    $lain->assignRole(UserRole::SuperAdmin->value);

    // Ada dua super admin: satu boleh diturunkan.
    actingAs($admin)->put("/users/{$lain->id}", [
        'name' => $lain->name,
        'email' => $lain->email,
        'role' => UserRole::Admin->value,
    ])->assertRedirect(route('users.index'));

    // Tersisa satu (akun sendiri) — dan akun sendiri memang tidak bisa diubah perannya.
    actingAs($admin)->put("/users/{$admin->id}", [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => UserRole::Admin->value,
    ])->assertSessionHas('error');

    expect($admin->refresh()->hasRole(UserRole::SuperAdmin->value))->toBeTrue();
});

it('blocks admins & owners from user management', function () {
    $admin = userWithRole(UserRole::Admin);
    actingAs($admin)->get('/users')->assertForbidden();

    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Owner->value);
    actingAs($owner)->get('/users')->assertForbidden();
});

it('lets any user change their own email and password', function () {
    $user = userWithRole(UserRole::Admin);

    actingAs($user)->put('/profile', [
        'name' => 'Nama Sendiri',
        'email' => 'sendiri@kos.test',
        'phone' => '08999999999',
    ])->assertRedirect();

    actingAs($user)->put('/profile/password', [
        'current_password' => 'password',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ])->assertRedirect();

    $user->refresh();

    expect($user->email)->toBe('sendiri@kos.test')
        ->and(Hash::check('passwordbaru', $user->password))->toBeTrue();
});

it('rejects a wrong current password on self-service change', function () {
    $user = userWithRole(UserRole::Admin);

    actingAs($user)->put('/profile/password', [
        'current_password' => 'salah',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

it('opens the profile page for a plain admin', function () {
    $admin = userWithRole(UserRole::Admin);

    actingAs($admin)->get('/settings')->assertOk();
});
