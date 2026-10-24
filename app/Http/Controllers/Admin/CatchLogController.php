<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCatchLogRequest;
use App\Models\CatchLog;
use App\Models\Fish;
use App\Support\FishImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Moderasi catatan tangkapan oleh admin. */
class CatchLogController extends Controller
{
    public function __construct(private readonly FishImageStorage $images)
    {
    }

    public function index(Request $request): View
    {
        $q = $this->textFilter($request->query('q'));
        $region = $this->enumFilter($request->query('wilayah'), Region::class);
        $like = $q === null ? null : '%'.addcslashes($q, '%_\\').'%';

        $logs = CatchLog::query()
            ->with('user:id,name,whatsapp_number')
            ->when($like, fn ($query) => $query->where(
                fn ($search) => $search
                    ->where('fish_name', 'like', $like)
                    ->orWhere('note', 'like', $like)
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', $like)
                        ->orWhere('whatsapp_number', 'like', $like))
            ))
            ->when($region, fn ($query, string $value) => $query->where('region', $value))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.catch-logs.index', [
            'logs' => $logs,
            'q' => $q,
            'region' => $region,
            'regions' => Region::cases(),
        ]);
    }

    public function edit(CatchLog $catchLog): View
    {
        return view('admin.catch-logs.form', [
            'log' => $catchLog->load('user:id,name,whatsapp_number'),
            'regions' => Region::cases(),
        ]);
    }

    public function update(AdminCatchLogRequest $request, CatchLog $catchLog): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['fish_name'] = trim($data['fish_name']);
        $oldImage = $catchLog->image_path;
        $newImage = null;
        $imageChanged = false;

        if ($request->hasFile('image')) {
            $newImage = $this->images->store($request->file('image'));
            $data['image_path'] = $newImage;
            $imageChanged = true;
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
            $imageChanged = true;
        }

        try {
            DB::transaction(function () use ($catchLog, $data, $oldImage, $newImage, $imageChanged): void {
                $catchLog->update($data);

                if ($imageChanged && $oldImage !== null && $oldImage !== $newImage) {
                    Fish::query()
                        ->where('image_url', $oldImage)
                        ->update(['image_url' => $newImage]);
                }
            });
        } catch (\Throwable $exception) {
            $this->images->deleteIfUnreferenced($newImage);
            throw $exception;
        }

        if ($imageChanged && $oldImage !== $newImage) {
            $this->images->deleteIfUnreferenced($oldImage);
        }

        return redirect()->route('admin.catch-logs.index')
            ->with('success', 'Catatan tangkapan berhasil diperbarui.');
    }
}
