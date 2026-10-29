<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\AlertService;
use App\Services\TideSimulator;
use App\Support\CurrentLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    public function __construct(
        private readonly TideSimulator $tide,
        private readonly CurrentLocation $current,
        private readonly AlertService $alerts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $location = $this->current->get();
        $status = $location ? $this->tide->status($location->id) : null;

        if ($user && $location && $status) {
            $this->alerts->syncForUser($user, $location, $status);
        }

        $reportCounts = collect();
        $recentReports = collect();
        if ($user && $user->isAdmin() && $location) {
            $reportCounts = Report::query()
                ->where('location_id', $location->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $recentReports = Report::query()
                ->with('user:id,name')
                ->where('location_id', $location->id)
                ->latest()
                ->limit(3)
                ->get();
        }

        return response()->json([
            'location' => $location?->only(['id', 'name', 'region']),
            'status' => $status,
            'offline' => (bool) $user?->mode_offline,
            'show_alert_banner' => $status && $status['level']->needsAlert() && (! $user || $user->notif_kondisi),
            'report_counts' => $reportCounts->toArray(),
            'recent_reports' => $recentReports->toArray(),
        ]);
    }

    public function prediction(Request $request): JsonResponse
    {
        $location = $this->current->get();
        $hours = $request->query('hours') === '48' ? 48 : 24;

        if (! $location) {
            return response()->json([
                'location' => null,
                'hours' => $hours,
                'prediction' => null,
                'history' => null,
            ]);
        }

        $prediction = $this->tide->prediction($location->id, $hours);
        $history = $this->tide->history($location->id);

        return response()->json([
            'location' => $location->only(['id', 'name', 'region']),
            'hours' => $hours,
            'prediction' => $prediction,
            'history' => $history,
        ]);
    }
}
