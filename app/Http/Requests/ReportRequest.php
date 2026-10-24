<?php

namespace App\Http\Requests;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    /** Hak akses per-laporan diperiksa lewat ReportPolicy di controller; di sini cukup sudah login. */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canCreateReports();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'category' => ['required', Rule::enum(ReportCategory::class)],
            'severity' => ['required', Rule::enum(ReportSeverity::class)],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul laporan wajib diisi.',
            'title.min' => 'Judul minimal 5 karakter.',
            'title.max' => 'Judul maksimal 150 karakter.',
            'category.required' => 'Pilih kategori laporan.',
            'category.enum' => 'Kategori tidak valid.',
            'severity.required' => 'Pilih tingkat keparahan.',
            'severity.enum' => 'Tingkat keparahan tidak valid.',
            'location_id.required' => 'Pilih lokasi kejadian.',
            'location_id.exists' => 'Lokasi tidak ditemukan.',
            'description.required' => 'Deskripsi kejadian wajib diisi.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
