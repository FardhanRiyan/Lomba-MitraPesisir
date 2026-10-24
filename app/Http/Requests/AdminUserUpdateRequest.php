<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Support\Format;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserUpdateRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'whatsapp_number' => [
                'required',
                'string',
                'regex:/^08\d{8,12}$/',
                Rule::unique('users', 'whatsapp_number')->ignore($user),
            ],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'role' => ['sometimes', 'required', Rule::in(UserRole::values())],
            'password' => ['nullable', 'string', 'min:6', 'max:72', 'confirmed'],
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
            'location_id.exists' => 'Lokasi desa pesisir tidak ditemukan.',
            'role.required' => 'Pilih peran.',
            'role.in' => 'Peran tidak valid.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.max' => 'Kata sandi maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ];
    }
}
