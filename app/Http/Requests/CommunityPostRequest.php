<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunityPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'area' => ['required', 'string', 'min:2', 'max:120'],
            'message' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'area.required' => 'Daerah wajib diisi.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal 5 karakter.',
            'message.max' => 'Pesan maksimal 1000 karakter.',
        ];
    }
}
