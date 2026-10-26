<?php

namespace App\Providers;

use App\Enums\AlertSource;
use App\Support\CurrentLocation;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Satu instance per request agar lokasi aktif tidak dihitung berulang.
        $this->app->scoped(CurrentLocation::class);
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        CarbonImmutable::setLocale(config('app.locale'));

        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        if ($this->app->environment('production')) {
            URL::forceHttps(str_starts_with((string) config('app.url'), 'https://'));
        }

        // Data bersama untuk header & navigasi (lokasi aktif + jumlah notifikasi belum dibaca).
        View::composer(['partials.header', 'partials.nav'], function (ViewContract $view) {
            $user = Auth::user();
            $view->with([
                'currentLocation' => app(CurrentLocation::class)->get(),
                'unreadAlerts' => $user
                    ? ($user->alerts()
                        ->when(! $user->isAdmin(), fn ($query) => $query->where('source', AlertSource::KONDISI->value))
                        ->whereNull('read_at')
                        ->count() + $user->userNotifications()->whereNull('read_at')->count())
                    : 0,
            ]);
        });
    }
}
