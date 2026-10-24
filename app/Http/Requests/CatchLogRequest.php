<?php

namespace App\Http\Requests;

use App\Enums\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatchLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fish_name' => ['required', 'string', 'min:2', 'max:80'],
            'quantity' => ['required', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:500'],
            'region' => ['required', Rule::enum(Region::class)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'fish_name.required' => 'Jenis ikan wajib diisi.',
            'fish_name.min' => 'Nama ikan minimal 2 karakter.',
            'fish_name.max' => 'Nama ikan maksimal 80 karakter.',
            'quantity.required' => 'Jumlah / berat wajib diisi.',
            'quantity.max' => 'Jumlah / berat maksimal 60 karakter.',
            'note.max' => 'Catatan maksimal 500 karakter.',
            'region.required' => 'Pilih wilayah tangkapan.',
            'region.enum' => 'Wilayah tangkapan tidak valid.',
            'image.image' => 'Unggah file gambar yang valid.',
            'image.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
        ];
    }
}
