<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FishRarity;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\FishRequest;
use App\Models\Fish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Katalog ikan (CRUD) oleh admin. */
class FishController extends Controller
{
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
        Fish::create($request->validated());

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan ditambahkan.');
    }

    public function edit(Fish $fish): View
    {
        return view('admin.fish.form', $this->formData($fish));
    }

    public function update(FishRequest $request, Fish $fish): RedirectResponse
    {
        $fish->update($request->validated());

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan diperbarui.');
    }

    public function destroy(Fish $fish): RedirectResponse
    {
        $imagePath = $fish->image_path;
        $fish->delete();
        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.fish.index')->with('success', 'Data ikan dihapus.');
    }

    private function formData(Fish $fish): array
    {
        return ['fish' => $fish, 'regions' => Region::cases(), 'rarities' => FishRarity::cases()];
    }
}
