<?php

namespace App\Http\Controllers;

use App\Enums\Region;
use App\Models\CatchLog;
use App\Models\Fish;
use App\Support\CurrentLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Informasi ketersediaan ikan per wilayah (publik) + riwayat tangkapan milik pengguna. */
class FishController extends Controller
{
    public function __construct(private readonly CurrentLocation $current)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        // Wilayah awal mengikuti lokasi aktif (default Jawa).
        $region = $this->enumFilter($request->query('wilayah'), Region::class)
            ?? ($this->current->get()?->region->value ?? Region::JAWA->value);
        $q = $this->textFilter($request->query('q'));

        $fish = Fish::query()
            ->search($q, $region)
            ->orderByRaw("FIELD(rarity, 'UMUM', 'CUKUP_UMUM', 'JARANG', 'SANGAT_JARANG')")
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $catchImages = CatchLog::query()
            ->whereNotNull('image_path')
            ->whereIn('fish_name', $fish->pluck('name'))
            ->where('region', $region)
            ->latest('id')
            ->get(['fish_name', 'image_path'])
            ->unique(fn (CatchLog $log) => mb_strtolower(trim($log->fish_name)))
            ->mapWithKeys(fn (CatchLog $log) => [
                mb_strtolower(trim($log->fish_name)) => Storage::disk('public')->url($log->image_path),
            ]);

        $logs = $user
            ? $user->catchLogs()->where('region', $region)
                ->latest()->orderByDesc('id')->paginate(5, ['*'], 'riwayat')->withQueryString()
            : null;

        return view('ikan.index', [
            'regions' => Region::cases(),
            'region' => $region,
            'q' => $q,
            'fish' => $fish,
            'catchImages' => $catchImages,
            'logs' => $logs,
            'prefillFish' => $this->textFilter($request->query('ikan')),
        ]);
    }
}
