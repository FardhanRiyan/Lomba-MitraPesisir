<?php

namespace App\Support;

use App\Models\CatchLog;
use App\Models\Fish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FishImageStorage
{
    public function store(UploadedFile $image): string
    {
        $path = $image->store('fish-photos', 'public');

        if ($path === false) {
            throw new RuntimeException('Foto ikan gagal disimpan.');
        }

        return $path;
    }

    public function deleteIfUnreferenced(?string $path): void
    {
        if (! $path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        if (Fish::query()->where('image_url', $path)->exists()
            || CatchLog::query()->where('image_path', $path)->exists()) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
