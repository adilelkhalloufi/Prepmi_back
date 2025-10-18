<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWeeklyMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add role-based authorization if needed
        // return $this->user()->hasRole('admin') || $this->user()->hasRole('manager');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $weeklyMenuId = $this->route('id');

        return [
            'week_start_date' => [
                'sometimes',
                'date',
                'date_format:Y-m-d',
                Rule::unique('weekly_menus', 'week_start_date')->ignore($weeklyMenuId),
                'after_or_equal:today'
            ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'title' => 'nullable|string',

            'meal_ids' => 'nullable|array|min:1',
            'meal_ids.*' => 'exists:meals,id|distinct'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'week_start_date.date' => 'The week start date must be a valid date.',
            'week_start_date.date_format' => 'The week start date must be in Y-m-d format.',
            'week_start_date.unique' => 'A weekly menu already exists for this date.',
            'week_start_date.after_or_equal' => 'The week start date cannot be in the past.',
            'meal_ids.array' => 'Meal IDs must be an array.',
            'meal_ids.min' => 'At least one meal must be selected.',
            'meal_ids.*.exists' => 'One or more selected meals do not exist.',
            'meal_ids.*.distinct' => 'Duplicate meal IDs are not allowed.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'week_start_date' => 'week start date',
            'meal_ids' => 'meal selection',
            'meal_ids.*' => 'meal ID'
        ];
    }
}
