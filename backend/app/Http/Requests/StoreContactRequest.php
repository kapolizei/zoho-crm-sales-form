<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'contact_name' => 'required|string|min:1',
            'company_name' => 'nullable|string',
            'contact_type' => 'required|string',
            'contact_persons' => 'required|array|min:1',
            'contact_persons.*.email' => 'required|email|min:3',
            'contact_persons.*.phone' => 'required|string|min:9',
        ];
    }
}
