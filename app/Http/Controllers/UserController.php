<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Identity\Domain\Enums\UserRole;
use Src\Identity\Domain\Models\User;
use Src\Identity\Domain\Repositories\UserRepositoryInterface;

class UserController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'users' => $this->users->paginate($request->only('search', 'role'))
                ->through(fn (User $user) => $this->present($user)),
            'filters' => $request->only('search', 'role'),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', ['roles' => $this->roleOptions()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->users->create(
            $request->safe()->only('name', 'email', 'phone', 'password'),
            $request->validated('role'),
        );

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', [
            'user' => $this->present($user),
            'roles' => $this->roleOptions(),
            'is_self' => $user->id === request()->user()->id,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $role = $request->validated('role');

        // Cegah admin mengunci diri sendiri dengan menurunkan peran akun yang sedang dipakai.
        if ($user->id === $request->user()->id && ! $user->hasRole($role)) {
            return back()->with('error', 'Peran akun sendiri tidak dapat diubah.');
        }

        if ($this->wouldRemoveLastSuperAdmin($user, $role)) {
            return back()->with('error', 'Minimal harus ada satu Super Admin.');
        }

        $this->users->update($user, $request->safe()->only('name', 'email', 'phone'), $role);

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->updatePassword($user, $request->validated('password'));

        return back()->with('success', "Password {$user->name} berhasil diubah.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Gate::before melewati policy untuk super admin, jadi jaga di sini juga.
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Akun sendiri tidak dapat dihapus.');
        }

        if ($this->wouldRemoveLastSuperAdmin($user, null)) {
            return back()->with('error', 'Minimal harus ada satu Super Admin.');
        }

        $this->users->delete($user);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    /** @return array{id:int,name:string,email:string,phone:string|null,role:string|null,created_at:string|null} */
    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->toDateString(),
        ];
    }

    /** @return array<int, array{value:string,label:string}> */
    private function roleOptions(): array
    {
        return array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            UserRole::cases(),
        );
    }

    /** Peran baru `null` berarti user akan dihapus. */
    private function wouldRemoveLastSuperAdmin(User $user, ?string $newRole): bool
    {
        $superAdmin = UserRole::SuperAdmin->value;

        if (! $user->hasRole($superAdmin) || $newRole === $superAdmin) {
            return false;
        }

        return $this->users->countByRole($superAdmin) <= 1;
    }
}
