<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'purchaseorder_number' => 'nullable|string',
            'date' => 'nullable|date',
            'vendor_id' => 'required|numeric',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|string',
            'line_items.*.quantity' => 'required|integer|min:1',
            'line_items.*.rate' => 'required|numeric|min:0',
            'line_items.*.description' => 'nullable|string',
            'shipment_date' => 'nullable|date',
        ];
    }
}
