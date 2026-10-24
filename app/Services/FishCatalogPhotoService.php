<?php

namespace App\Services;

use App\Enums\Region;
use App\Models\Fish;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class FishCatalogPhotoService
{
    private const MIN_SIMILARITY = 90;

    /**
     * Promote a catch photo to the matching catalog fish when it has no photo.
     */
    public function addPhotoToMatchingFish(string $fishName, Region $region, string $catchImagePath): ?string
    {
        $matches = Fish::query()
            ->where('region', $region->value)
            ->get()
            ->map(fn (Fish $fish): array => [
                'fish' => $fish,
                'similarity' => self::similarity($fishName, $fish->name),
            ])
            ->filter(fn (array $match): bool => $match['similarity'] >= self::MIN_SIMILARITY)
            ->sortByDesc('similarity')
            ->values();

        if ($matches->isEmpty()) {
            return null;
        }

        $bestMatch = $matches->first();
        $runnerUp = $matches->get(1);
        if ($runnerUp !== null && $bestMatch['similarity'] - $runnerUp['similarity'] < 5) {
            return null;
        }

        /** @var Fish $fish */
        $fish = $bestMatch['fish'];
        if (filled($fish->image_url) || filled($fish->image_path)) {
            return null;
        }

        $extension = pathinfo($catchImagePath, PATHINFO_EXTENSION);
        $catalogImagePath = 'fish-photos/'.Str::uuid().($extension !== '' ? '.'.$extension : '');
        $disk = Storage::disk('public');

        if (! $disk->copy($catchImagePath, $catalogImagePath)) {
            throw new RuntimeException('Foto ikan gagal disalin ke katalog.');
        }

        try {
            $fish->image_path = $catalogImagePath;
            if (! $fish->save()) {
                throw new RuntimeException('Path foto ikan gagal disimpan ke katalog.');
            }
        } catch (Throwable $exception) {
            $disk->delete($catalogImagePath);

            throw $exception;
        }

        return $catalogImagePath;
    }

    public static function similarity(string $first, string $second): int
    {
        $first = self::normalizeName($first);
        $second = self::normalizeName($second);

        if ($first === '' || $second === '') {
            return 0;
        }

        if ($first === $second) {
            return 100;
        }

        if (mb_strlen($first) < 5 || mb_strlen($second) < 5) {
            return 0;
        }

        similar_text($first, $second, $percentage);

        return (int) round($percentage);
    }

    private static function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $name) ?? '';
    }
}
