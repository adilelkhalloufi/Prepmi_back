<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'order_date' => 'sometimes|date',
            'delivery_date' => 'sometimes|date|after_or_equal:order_date',
            'status' => 'sometimes|in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled',
            'total_price' => 'sometimes|numeric|min:0',
            'address_id' => 'sometimes|exists:addresses,id',
            'notes' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'meals' => 'sometimes|array|min:1',
            'meals.*.meal_id' => 'required_with:meals|exists:meals,id',
            'meals.*.quantity' => 'required_with:meals|integer|min:1',
            'meals.*.price' => 'required_with:meals|numeric|min:0',
        ];
    }
}
