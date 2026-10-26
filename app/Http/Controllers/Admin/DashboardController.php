<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportCategory;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CatchLog;
use App\Models\CommunityPost;
use App\Models\Fish;
use App\Models\Location;
use App\Models\Report;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\TideSimulator;
use App\Support\CurrentLocation;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Ringkasan statistik & monitoring untuk admin. */
class DashboardController extends Controller
{
    public function __construct(
        private readonly TideSimulator $tide,
        private readonly CurrentLocation $current,
    ) {
    }

    public function index(): View
    {
        $byStatus = Report::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $byCategory = Report::query()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category');
        $byRole = User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        $topLocations = Report::query()
            ->select('location_id', DB::raw('count(*) as total'))
            ->groupBy('location_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('location:id,name')
            ->get();

        return view('admin.dashboard', [
            'totals' => [
                'Pengguna' => User::count(),
                'Lokasi' => Location::count(),
                'Jenis ikan' => Fish::count(),
                'Laporan' => Report::count(),
                'Tangkapan' => CatchLog::count(),
                'Pesan komunitas' => CommunityPost::count(),
            ],
            'statusRows' => collect(ReportStatus::cases())->map(fn (ReportStatus $s) => [
                'label' => $s->label(),
                'total' => (int) ($byStatus[$s->value] ?? 0),
            ]),
            'categoryRows' => collect(ReportCategory::cases())->map(fn (ReportCategory $c) => [
                'label' => $c->label(),
                'total' => (int) ($byCategory[$c->value] ?? 0),
            ]),
            'roleRows' => collect(UserRole::cases())->map(fn (UserRole $r) => [
                'label' => $r->label(),
                'total' => (int) ($byRole[$r->value] ?? 0),
            ]),
            'topLocations' => $topLocations,
            'latestReports' => Report::query()->with(['location:id,name', 'user:id,name'])->latest()->orderByDesc('id')->limit(5)->get(),
            'shareUsers' => User::query()
                ->where('role', UserRole::USER->value)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'whatsapp_number']),
            ...$this->defaultShareContent(),
        ]);
    }

    public function shareInformation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_mode' => ['sometimes', 'in:selected,all'],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'user_ids' => ['required_unless:recipient_mode,all', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $recipientMode = $data['recipient_mode'] ?? 'selected';
        $recipientCount = DB::transaction(function () use ($recipientMode, $data): int {
            $users = User::query()->where('role', UserRole::USER->value);
            if ($recipientMode === 'selected') {
                $users->whereIn('id', $data['user_ids']);
            }

            $recipientIds = $users->orderBy('id')->lockForUpdate()->pluck('id');
            if ($recipientMode === 'selected' && $recipientIds->count() !== count($data['user_ids'])) {
                throw ValidationException::withMessages(['user_ids' => 'Pilih akun user yang valid sebagai penerima.']);
            }
            if ($recipientIds->isEmpty()) {
                throw ValidationException::withMessages(['user_ids' => 'Belum ada user yang dapat menerima informasi.']);
            }

            $sentAt = now();
            foreach ($recipientIds->chunk(500) as $recipientChunk) {
                UserNotification::query()->insert($recipientChunk->map(fn (int $userId) => [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'created_at' => $sentAt,
                    'updated_at' => $sentAt,
                ])->all());
            }

            return $recipientIds->count();
        });

        return redirect()->route('admin.dashboard')->with(
            'success',
            "Informasi berhasil dikirim melalui notifikasi aplikasi kepada {$recipientCount} user."
        );
    }

    /**
     * @return array{shareTitle: string, shareMessage: string}
     */
    private function defaultShareContent(): array
    {
        $location = $this->current->get();
        if ($location === null) {
            return [
                'shareTitle' => 'Informasi MitraPesisir',
                'shareMessage' => '',
            ];
        }

        $status = $this->tide->status($location->id);
        $trend = ['NAIK' => 'air sedang naik', 'TURUN' => 'air sedang turun', 'STABIL' => 'air relatif stabil'];

        return [
            'shareTitle' => 'Info pasang surut - '.$location->name,
            'shareMessage' => sprintf(
                'Info MitraPesisir - %s: status %s, tinggi air %s m (%s). Angin %d km/j, gelombang %s m. Diperbarui %s WIB.',
                $location->name,
                $status['level']->label(),
                Format::meters($status['waterLevel']),
                $trend[$status['trend']],
                $status['windSpeedKmh'],
                Format::meters($status['waveHeightM'], 1),
                Format::clock($status['recordedAt']),
            ),
        ];
    }
}
