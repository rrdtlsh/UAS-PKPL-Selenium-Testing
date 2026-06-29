<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConditionDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autentikasi diserahkan ke middleware di routes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * inputted_by tidak divalidasi di sini karena nilainya selalu diambil
     * dari Auth::id() di controller/service — bukan dari input frontend.
     * Ini mencegah user memanipulasi inputted_by melalui request body.
     */
    public function rules(): array
    {
        return [
            'storage_room_id' => 'required|exists:storage_rooms,id',
            'temperature'     => 'required|numeric|min:-50|max:100',
            'humidity'        => 'required|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'storage_room_id.required' => 'Ruang penyimpanan wajib dipilih.',
            'storage_room_id.exists'   => 'Ruang penyimpanan tidak valid dalam sistem.',
            'temperature.required'     => 'Suhu ruangan wajib diisi.',
            'temperature.numeric'      => 'Suhu ruangan harus berupa format angka.',
            'temperature.min'          => 'Suhu ruangan tidak boleh kurang dari -50 derajat.',
            'temperature.max'          => 'Suhu ruangan tidak boleh melebihi 100 derajat.',
            'humidity.required'        => 'Kelembaban ruangan wajib diisi.',
            'humidity.numeric'         => 'Kelembaban ruangan harus berupa format angka.',
            'humidity.min'             => 'Tingkat kelembaban minimum adalah 0%.',
            'humidity.max'             => 'Tingkat kelembaban maksimum adalah 100%.',
        ];
    }
}
