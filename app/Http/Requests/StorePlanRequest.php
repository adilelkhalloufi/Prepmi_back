<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Update with your authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:plans,slug',
            'meals_per_week' => 'required|integer|min:1',
            'price_per_week' => 'required|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_free_shipping' => 'boolean',
            'is_featured' => 'boolean',
            'points_value' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
        ];
    }
}
