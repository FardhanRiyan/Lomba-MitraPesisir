<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Manajemen pengguna oleh admin: cari, filter peran, ubah peran, hapus. */
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

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(
            ['role' => ['required', 'in:'.implode(',', UserRole::values())]],
            ['role.required' => 'Pilih peran.', 'role.in' => 'Peran tidak valid.'],
        );

        if ($user->is($request->user())) {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri.');
        }
        $user->update($data);

        return redirect()->back()->with('success', "Peran {$user->name} diubah menjadi ".$user->role->label().'.');
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
