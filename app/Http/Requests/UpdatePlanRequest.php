<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Update with your authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('plans')->ignore($this->plan)],
            'meals_per_week' => 'sometimes|integer|min:1',
            'price_per_week' => 'sometimes|numeric|min:0',
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
