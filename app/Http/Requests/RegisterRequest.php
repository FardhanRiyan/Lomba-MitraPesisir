<?php

namespace App\Http\Requests;

use App\Support\Format;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'whatsapp_number' => ['required', 'string', 'regex:/^08\d{8,12}$/', 'unique:users,whatsapp_number'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
            'agree' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp tidak valid (contoh: 0867 6767 6767).',
            'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
            'location_id.required' => 'Pilih lokasi desa pesisir.',
            'location_id.exists' => 'Lokasi desa pesisir tidak ditemukan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'agree.accepted' => 'Anda perlu menyetujui Syarat & Ketentuan dan Kebijakan Privasi.',
        ];
    }
}
