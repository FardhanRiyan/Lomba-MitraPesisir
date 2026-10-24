<?php

namespace App\Http\Controllers;

use App\Enums\Region;
use App\Http\Requests\CatchLogRequest;
use App\Models\CatchLog;
use App\Services\FishCatalogPhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Catat & hapus riwayat tangkapan milik sendiri. */
class CatchLogController extends Controller
{
    public function store(CatchLogRequest $request, FishCatalogPhotoService $fishPhotos): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('image');
        $data['location_id'] = $user->location_id;
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('catch-photos', 'public');
            if ($imagePath === false) {
                throw new \RuntimeException('Foto tangkapan gagal disimpan.');
            }
            $data['image_path'] = $imagePath;
        }

        $catalogImagePath = null;
        try {
            DB::transaction(function () use ($user, $data, $imagePath, $fishPhotos, &$catalogImagePath): void {
                $user->catchLogs()->create($data);

                if ($imagePath !== null) {
                    $catalogImagePath = $fishPhotos->addPhotoToMatchingFish(
                        $data['fish_name'],
                        Region::from($data['region']),
                        $imagePath,
                    );
                }
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }
            if ($catalogImagePath !== null) {
                Storage::disk('public')->delete($catalogImagePath);
            }

            throw $exception;
        }

        $message = $catalogImagePath !== null
            ? 'Tangkapan berhasil dicatat. Foto ikan di katalog juga diperbarui.'
            : 'Tangkapan berhasil dicatat.';

        return redirect()->to(route('ikan.index').'#riwayat')->with('success', $message);
    }

    public function destroy(CatchLog $catchLog): RedirectResponse
    {
        Gate::authorize('delete', $catchLog);
        if ($catchLog->image_path !== null) {
            Storage::disk('public')->delete($catchLog->image_path);
        }
        $catchLog->delete();

        return redirect()->to(route('ikan.index').'#riwayat')->with('success', 'Catatan tangkapan dihapus.');
    }
}
