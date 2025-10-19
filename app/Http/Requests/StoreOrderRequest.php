<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:order_date',
            'status' => 'nullable|in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled',
            'total_price' => 'required|numeric|min:0',
            'address_id' => 'required|exists:addresses,id',
            'notes' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'meals' => 'required|array|min:1',
            'meals.*.meal_id' => 'required|exists:meals,id',
            'meals.*.quantity' => 'required|integer|min:1',
            'meals.*.price' => 'required|numeric|min:0',
        ];
    }
}
