<?php

namespace App\Http\Controllers;

use App\Enums\FishRarity;
use App\Enums\Region;
use App\Models\Fish;
use App\Support\CurrentLocation;
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
        $region = $this->enumFilter($request->query('wilayah'), Region::class);
        $catchRegion = $region ?? $this->current->get()?->region->value ?? Region::JAWA->value;
        $q = $this->textFilter($request->query('q'));
        $showAllFish = $request->boolean('semua');

        $fishQuery = Fish::query()
            ->search($q, $region)
            ->orderByRaw(
                'CASE rarity WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 ELSE 5 END',
                array_map(fn (FishRarity $rarity): string => $rarity->value, FishRarity::cases()),
            )
            ->orderBy('region')
            ->orderBy('name');
        $fishCount = (clone $fishQuery)->count();
        $fish = $showAllFish ? $fishQuery->get() : $fishQuery->limit(5)->get();

        $logs = $user
            ? $user->catchLogs()
                ->when($region, fn ($query, string $value) => $query->where('region', $value))
                ->latest()->orderByDesc('id')->paginate(5, ['*'], 'riwayat')->withQueryString()
            : null;

        return view('ikan.index', [
            'regions' => Region::cases(),
            'region' => $region,
            'catchRegion' => $catchRegion,
            'q' => $q,
            'fish' => $fish,
            'fishCount' => $fishCount,
            'showAllFish' => $showAllFish,
            'logs' => $logs,
            'prefillFish' => $this->textFilter($request->query('ikan')),
        ]);
    }
}
