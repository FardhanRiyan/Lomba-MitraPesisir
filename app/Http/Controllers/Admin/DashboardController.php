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
use App\Services\TideSimulator;
use App\Services\WhatsAppCloudApi;
use App\Support\CurrentLocation;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Ringkasan statistik & monitoring untuk admin. */
class DashboardController extends Controller
{
    public function __construct(
        private readonly TideSimulator $tide,
        private readonly CurrentLocation $current,
        private readonly WhatsAppCloudApi $whatsApp,
    ) {
    }

    public function index(): View
    {
        $location = $this->current->get();
        $whatsAppRecipients = $this->whatsAppRecipients();
        $sharePreview = null;
        if ($location !== null) {
            $sharePreview = $this->informationMessage($location->name, $this->tide->status($location->id));
        }

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
            'whatsappConfigured' => $this->whatsApp->isConfigured(),
            'whatsAppRecipientCount' => $whatsAppRecipients->count(),
            'sharePreview' => $sharePreview,
        ]);
    }

    public function shareInformation(): RedirectResponse
    {
        $location = $this->current->get();
        if ($location === null) {
            throw ValidationException::withMessages(['share' => 'Belum ada lokasi pesisir untuk dibagikan.']);
        }

        $status = $this->tide->status($location->id);
        $message = $this->informationMessage($location->name, $status);

        $recipients = $this->whatsAppRecipients();
        if (! $recipients->exists()) {
            return redirect()->route('admin.dashboard')->with('warning', 'Belum ada user dengan nomor WhatsApp yang menyetujui pengiriman info.');
        }

        $result = $this->whatsApp->broadcast($recipients, $message);
        Log::info('WhatsApp info broadcast completed.', ['sent' => $result['sent'], 'failed' => $result['failed']]);

        if ($result['failed'] > 0) {
            return redirect()->route('admin.dashboard')->with(
                'error',
                "WhatsApp menerima permintaan pengiriman untuk {$result['sent']} user; gagal untuk {$result['failed']} user. Periksa log aplikasi dan konfigurasi Meta Cloud API."
            );
        }

        return redirect()->route('admin.dashboard')->with(
            'success',
            "WhatsApp menerima permintaan pengiriman info pasang surut untuk {$result['sent']} user."
        );
    }

    private function informationMessage(string $locationName, array $status): string
    {
        $trend = ['NAIK' => 'air sedang naik', 'TURUN' => 'air sedang turun', 'STABIL' => 'air relatif stabil'];
        return sprintf(
            'Info MitraPesisir - %s: status %s, tinggi air %s m (%s). Angin %d km/j, gelombang %s m. Diperbarui %s WIB.',
            $locationName,
            $status['level']->label(),
            Format::meters($status['waterLevel']),
            $trend[$status['trend']],
            $status['windSpeedKmh'],
            Format::meters($status['waveHeightM'], 1),
            Format::clock($status['recordedAt']),
        );
    }

    private function whatsAppRecipients(): Builder
    {
        return User::query()
            ->where('role', UserRole::USER->value)
            ->where('whatsapp_opt_in', true)
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '<>', '')
            ->orderBy('id');
    }
}
