<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Src\Identity\Domain\Models\User;
use Src\Identity\Domain\Repositories\UserRepositoryInterface;

/** Pengaturan akun milik user yang sedang login (nama, email, password). */
class ProfileController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->users->update($user, $request->safe()->only('name', 'email', 'phone'));

        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->users->updatePassword($user, $request->validated('password'));

        return back()->with('success', 'Password berhasil diubah.');
    }
}
