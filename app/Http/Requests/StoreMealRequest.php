<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null
        $input = $this->convertEmptyStringsToNull($this->all());
        $this->merge($input);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'calories' => 'nullable|integer|min:0',
            'protein' => 'nullable|numeric|min:0',
            'carbohydrates' => 'nullable|numeric|min:0',
            'fats' => 'nullable|numeric|min:0',
            'fiber' => 'nullable|numeric|min:0',
            'sodium' => 'nullable|numeric|min:0',
            'sugar' => 'nullable|numeric|min:0',
            'ingredients' => 'string',
            'allergens' => 'nullable|array',
            'allergens.*' => 'string',
            'preparation_instructions' => 'nullable|string',
            'storage_instructions' => 'nullable|string',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_gluten_free' => 'boolean',
            'is_dairy_free' => 'boolean',
            'is_nut_free' => 'boolean',
            'is_keto' => 'boolean',
            'is_paleo' => 'boolean',
            'is_low_carb' => 'boolean',
            'is_high_protein' => 'boolean',
            'is_spicy' => 'boolean',
            'spice_level' => 'nullable|integer|min:0|max:5',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cooking_time_minutes' => 'nullable|integer|min:0',
            'difficulty_level' => 'nullable|integer|min:1|max:5',
            'chef_notes' => 'nullable|string',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date|after_or_equal:available_from',
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'cost_per_serving' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'serving_size' => 'nullable|string|max:255',
            'category_id' => ['nullable', 'integer'],
            'type_id' => ['nullable', 'integer', Rule::in([0, 1, 2])],
            'is_membership' => 'nullable|boolean',
        ];
    }

    /**
     * Convert empty strings to null recursively
     */
    private function convertEmptyStringsToNull(array $data): array
    {
        return array_map(function ($value) {
            if ($value === '') {
                return null;
            }
            if (is_array($value)) {
                return $this->convertEmptyStringsToNull($value);
            }
            return $value;
        }, $data);
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The meal name is required.',
            'image_path.image' => 'The file must be an image.',
            'image_path.max' => 'The image size must not exceed 2MB.',
            'gallery_images.*.image' => 'All gallery files must be images.',
            'gallery_images.*.max' => 'Each gallery image must not exceed 2MB.',
            'category_id.in' => 'Invalid category. Must be 1 (Menu), 2 (Breakfast), or 3 (Drinks).',
        ];
    }
}
