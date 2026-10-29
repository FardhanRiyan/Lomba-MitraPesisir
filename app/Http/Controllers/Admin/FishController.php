<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FishRarity;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\FishRequest;
use App\Models\Fish;
use App\Support\FishImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fcades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Katalog ikan (CRUD) oleh admin. */
class FishController extends Controller
{
    public function __construct(private readonly FishImageStorage $images)
    {
    }

    public function index(Request $request): View
    {
        $q = $this->textFilter($request->query('q'));
        $region = $this->enumFilter($request->query('wilayah'), Region::class);

        return view('admin.fish.index', [
            'fish' => Fish::query()->search($q, $region)->orderBy('region')->orderBy('name')->paginate(10)->withQueryString(),
            'q' => $q,
            'region' => $region,
            'regions' => Region::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.fish.form', $this->formData(new Fish(['rarity' => FishRarity::UMUM, 'region' => Region::JAWA])));
    }

    public function store(FishRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagePath = $this->storeImage($request, $data);
        unset($data['image'], $data['remove_image']);

        try {
            Fish::create($data);
        } catch (\Throwable $exception) {
            $this->images->deleteIfUnreferenced($imagePath);
            throw $exception;
        }

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan ditambahkan.');
    }

    public function edit(Fish $fish): View
    {
        return view('admin.fish.form', $this->formData($fish));
    }

    public function update(FishRequest $request, Fish $fish): RedirectResponse
    {
        $data = $request->validated();
        $oldImage = $fish->image_url;
        $newImage = $this->storeImage($request, $data, $fish);
        unset($data['image'], $data['remove_image']);

        try {
            $fish->update($data);
        } catch (\Throwable $exception) {
            $this->images->deleteIfUnreferenced($newImage);
            throw $exception;
        }

        if ($oldImage !== $fish->image_url) {
            $this->images->deleteIfUnreferenced($oldImage);
        }

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan diperbarui.');
    }

    public function destroy(Fish $fish): RedirectResponse
    {
        $image = $fish->image_url;
        $deleted = DB::transaction(
            fn (): int => Fish::query()->whereKey($fish->getKey())->delete()
        );

        if ($deleted !== 1) {
            throw new \RuntimeException('Data ikan gagal dihapus dari katalog.');
        }

        $this->images->deleteIfUnreferenced($image);

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan dihapus.');
    }

    private function storeImage(FishRequest $request, array &$data, ?Fish $fish = null): ?string
    {
        if ($request->hasFile('image')) {
            $data['image_url'] = $this->images->store($request->file('image'));

            return $data['image_url'];
        }

        if ($request->boolean('remove_image')) {
            $data['image_url'] = null;
        } elseif ($data['image_url'] ?? null) {
            return null;
        } elseif ($fish !== null) {
            unset($data['image_url']);
        }

        return null;
    }

    private function formData(Fish $fish): array
    {
        return ['fish' => $fish, 'regions' => Region::cases(), 'rarities' => FishRarity::cases()];
    }
}
