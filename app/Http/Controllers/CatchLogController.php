<?php

namespace App\Http\Controllers;

use App\Enums\FishRarity;
use App\Http\Requests\CatchLogRequest;
use App\Models\CatchLog;
use App\Models\Fish;
use App\Support\FishImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Catat & hapus riwayat tangkapan milik sendiri. */
class CatchLogController extends Controller
{
    public function __construct(private readonly FishImageStorage $images)
    {
    }

    public function store(CatchLogRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('image');
        $data['fish_name'] = trim($data['fish_name']);
        $data['location_id'] = $user->location_id;
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $this->images->store($request->file('image'));
            $data['image_path'] = $imagePath;
        }

        try {
            DB::transaction(function () use ($user, $data, $imagePath): void {
                $user->catchLogs()->create($data);

                if ($imagePath === null) {
                    return;
                }

                $name = trim($data['fish_name']);
                $fish = Fish::query()
                    ->where('region', $data['region'])
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                    ->first();

                if ($fish === null) {
                    Fish::create([
                        'name' => $name,
                        'description' => 'Data ikan ini ditambahkan oleh anggota komunitas.',
                        'rarity' => FishRarity::UMUM,
                        'region' => $data['region'],
                        'image_url' => $imagePath,
                    ]);
                } elseif (! $fish->image_url) {
                    $fish->update(['image_url' => $imagePath]);
                }
            });
        } catch (\Throwable $exception) {
            $this->images->deleteIfUnreferenced($imagePath);
            throw $exception;
        }

        return redirect()->to(route('ikan.index').'#riwayat')->with('success', 'Tangkapan berhasil dicatat.');
    }

    public function destroy(CatchLog $catchLog): RedirectResponse
    {
        Gate::authorize('delete', $catchLog);
        $imagePath = $catchLog->image_path;
        $catchLog->delete();
        $this->images->deleteIfUnreferenced($imagePath);

        return redirect()->to(route('ikan.index').'#riwayat')->with('success', 'Catatan tangkapan dihapus.');
    }
}
