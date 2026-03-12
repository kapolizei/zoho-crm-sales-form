<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'name'               => 'required|string',
            'item_type'          => 'required|in:inventory,service,non_inventory',
            'vendor_id' => 'nullable|string',
            'vendor_name' => 'required|string|min:1',
            'rate'               => 'required|numeric|min:0',
            'description'        => 'nullable|string',
            'sku'                => 'nullable|string',
            'unit'               => 'nullable|string',
            'purchase_rate'      => 'nullable|numeric',
            'reorder_level'      => 'nullable|integer',
            'initial_stock'      => 'nullable|numeric',
            'initial_stock_rate' => 'nullable|numeric',
        ];
    }
}
