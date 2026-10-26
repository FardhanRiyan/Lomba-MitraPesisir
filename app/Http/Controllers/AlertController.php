<?php

namespace App\Http\Controllers;

use App\Enums\AlertSource;
use App\Models\Alert;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Notifikasi peringatan: daftar, tandai dibaca, hapus. Hanya milik pengguna sendiri. */
class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $onlyUnread = $request->query('filter') === 'belum';
        $user = $request->user();

        $alerts = $user->alerts()
            ->with('location:id,name')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('source', AlertSource::KONDISI->value))
            ->when($onlyUnread, fn ($q) => $q->whereNull('read_at'))
            ->latest()->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $userNotifications = $user->userNotifications()
            ->when($onlyUnread, fn ($q) => $q->whereNull('read_at'))
            ->latest()->orderByDesc('id')
            ->paginate(5, ['*'], 'message_page')
            ->withQueryString();

        return view('notifikasi.index', [
            'alerts' => $alerts,
            'userNotifications' => $userNotifications,
            'onlyUnread' => $onlyUnread,
            'unreadTotal' => $user->alerts()
                ->when(! $user->isAdmin(), fn ($q) => $q->where('source', AlertSource::KONDISI->value))
                ->whereNull('read_at')
                ->count() + $user->userNotifications()->whereNull('read_at')->count(),
        ]);
    }

    public function read(Request $request, Alert $alert): RedirectResponse
    {
        abort_unless($alert->user_id === $request->user()->id, 404);
        abort_if(! $request->user()->isAdmin() && $alert->source !== AlertSource::KONDISI, 404);
        if (! $alert->isRead()) {
            $alert->update(['read_at' => now()]);
        }

        return redirect()->back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->alerts()
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('source', AlertSource::KONDISI->value))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $request->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('alerts.index')->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function readUserNotification(Request $request, UserNotification $userNotification): RedirectResponse
    {
        abort_unless($userNotification->user_id === $request->user()->id, 404);
        if (! $userNotification->isRead()) {
            $userNotification->update(['read_at' => now()]);
        }

        return redirect()->back();
    }

    public function destroyUserNotification(Request $request, UserNotification $userNotification): RedirectResponse
    {
        abort_unless($userNotification->user_id === $request->user()->id, 404);
        $userNotification->delete();

        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }

    public function destroy(Request $request, Alert $alert): RedirectResponse
    {
        abort_unless($alert->user_id === $request->user()->id, 404);
        abort_if(! $request->user()->isAdmin() && $alert->source !== AlertSource::KONDISI, 404);
        $alert->delete();

        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }
}
