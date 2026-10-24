<?php

namespace App\Http\Requests;

use App\Support\Format;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['whatsapp_number' => Format::normalizeWhatsapp($this->input('whatsapp_number'))]);
    }

    public function rules(): array
    {
        return [
            'whatsapp_number' => ['required', 'string', 'regex:/^08\d{8,12}$/'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp tidak valid (contoh: 0867 6767 6767).',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /** Coba login; batasi 5 percobaan gagal per nomor + IP. */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = ['whatsapp_number' => $this->input('whatsapp_number'), 'password' => $this->input('password')];
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'whatsapp_number' => 'Nomor WhatsApp atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'whatsapp_number' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('whatsapp_number')).'|'.$this->ip());
    }
}
