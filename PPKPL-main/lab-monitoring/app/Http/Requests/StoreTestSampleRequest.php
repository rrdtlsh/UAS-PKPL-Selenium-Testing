<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestSampleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Untuk tahap ini, kita asumsikan semua user yang sudah login bisa menambahkan sampel
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sample_code'     => 'required|string|unique:test_samples,sample_code|max:50',
            'sample_name'     => 'required|string|max:255',
            'storage_room_id' => 'required|exists:storage_rooms,id',
            'stored_at'       => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia (sesuai User Story).
     */
    public function messages(): array
    {
        return [
            'sample_code.required'      => 'Kode sampel wajib diisi.',
            'sample_code.unique'        => 'Kode sampel ini sudah terdaftar dalam sistem.',
            'sample_code.max'           => 'Kode sampel tidak boleh lebih dari 50 karakter.',
            'sample_name.required'      => 'Nama sampel wajib diisi.',
            'sample_name.max'           => 'Nama sampel tidak boleh lebih dari 255 karakter.',
            'storage_room_id.required'  => 'Ruang penyimpanan wajib dipilih.',
            'storage_room_id.exists'    => 'Ruang penyimpanan yang dipilih tidak valid atau tidak ditemukan.',
            'stored_at.required'        => 'Waktu penyimpanan wajib diisi.',
            'stored_at.date'            => 'Format waktu penyimpanan tidak valid.',
            'stored_at.before_or_equal' => 'Waktu penyimpanan tidak boleh lebih dari hari ini (masa depan).',
        ];
    }
}
