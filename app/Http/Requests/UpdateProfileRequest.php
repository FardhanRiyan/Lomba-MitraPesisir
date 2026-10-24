<?php

namespace App\Http\Requests;

use App\Support\Format;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'whatsapp_number' => Format::normalizeWhatsapp($this->input('whatsapp_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'whatsapp_number' => [
                'required', 'string', 'regex:/^08\d{8,12}$/',
                Rule::unique('users', 'whatsapp_number')->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp tidak valid (contoh: 0867 6767 6767).',
            'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah dipakai akun lain.',
        ];
    }
}
