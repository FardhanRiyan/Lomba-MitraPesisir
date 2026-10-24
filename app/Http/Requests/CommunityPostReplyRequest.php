<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunityPostReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Balasan wajib diisi.',
            'message.min' => 'Balasan minimal 2 karakter.',
            'message.max' => 'Balasan maksimal 1000 karakter.',
        ];
    }
}
