<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCorrectiveActionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'incident_ticket_id' => 'required|exists:incident_tickets,id',
            'description' => 'required|string|min:3',
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Deskripsi tindakan tidak boleh kosong',
            'description.min' => 'Deskripsi tindakan minimal 3 karakter',
        ];
    }
}