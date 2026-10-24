<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Manajemen pengguna oleh admin: cari, edit, dan hapus. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = $this->textFilter($request->query('q'));
        $role = $this->enumFilter($request->query('peran'), UserRole::class);

        return view('admin.users.index', [
            'users' => User::query()->with('location:id,name')->search($q, $role)->orderBy('id')->paginate(10)->withQueryString(),
            'q' => $q,
            'role' => $role,
            'roles' => UserRole::cases(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is($request->user()) && isset($data['role']) && $data['role'] !== $user->role->value) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Pengguna {$name} dihapus.");
    }
}
