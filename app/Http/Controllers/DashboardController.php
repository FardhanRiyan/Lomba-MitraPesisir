<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\AlertService;
use App\Services\TideSimulator;
use App\Support\CurrentLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Beranda: status & monitoring air laut untuk lokasi aktif + ringkasan laporan. */
class DashboardController extends Controller
{
    public function __construct(
        private readonly TideSimulator $tide,
        private readonly CurrentLocation $current,
        private readonly AlertService $alerts,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $this->current->get();
        $status = $location ? $this->tide->status($location->id) : null;

        // Peringatan otomatis: bila Waspada/Bahaya, simpan ke notifikasi pengguna.
        if ($user && $location && $status) {
            $this->alerts->syncForUser($user, $location, $status);
        }

        $reportCounts = collect();
        $recentReports = collect();
        if ($location && $user?->isAdmin()) {
            $reportCounts = Report::query()
                ->where('location_id', $location->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
            $recentReports = Report::query()
                ->with('user:id,name')
                ->where('location_id', $location->id)
                ->latest()->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        $trendText = ['NAIK' => 'air sedang naik', 'TURUN' => 'air sedang turun', 'STABIL' => 'air relatif stabil'];

        return view('beranda.index', [
            'location' => $location,
            'status' => $status,
            'offline' => (bool) $user?->mode_offline,
            'showAlertBanner' => $status && $status['level']->needsAlert() && (! $user || $user->notif_kondisi),
            'reportCounts' => $reportCounts,
            'recentReports' => $recentReports,
            'trendText' => $trendText,
        ]);
    }
}
