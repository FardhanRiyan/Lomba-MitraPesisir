<?php

namespace App\Http\Requests;

use App\Enums\FishRarity;
use App\Enums\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $url = trim((string) $this->input('image_url'));
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'image_url' => $url === '' ? null : $url,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:2', 'max:80',
                // Kombinasi (nama, wilayah) harus unik; saat edit, abaikan data itu sendiri.
                Rule::unique('fish', 'name')->where('region', $this->input('region'))->ignore($this->route('fish')?->id),
            ],
            'region' => ['required', Rule::enum(Region::class)],
            'rarity' => ['required', Rule::enum(FishRarity::class)],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama ikan wajib diisi.',
            'name.unique' => 'Ikan ini sudah ada untuk wilayah tersebut.',
            'region.required' => 'Pilih wilayah.',
            'region.enum' => 'Wilayah tidak valid.',
            'rarity.required' => 'Pilih tingkat kelangkaan.',
            'rarity.enum' => 'Tingkat kelangkaan tidak valid.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.min' => 'Deskripsi minimal 5 karakter.',
            'image_url.url' => 'Tautan foto harus berupa URL yang valid.',
            'image.image' => 'Unggah file gambar yang valid.',
            'image.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
        ];
    }
}
