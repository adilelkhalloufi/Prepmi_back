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
            'infos' => 'array',
            'infos.firstName' => 'required|string|max:255',
            'infos.lastName' => 'required|string|max:255',
            'infos.phoneNumber' => 'required|string|max:20',
            'infos.address' => 'required|string|max:500',
            'infos.email' => 'nullable|email|max:255',
            'infos.password' => 'nullable|string|max:255',
            'meals' => 'array',
            'meals.*.id' => 'required|integer|exists:meals,id',
            'meals.*.quantity' => 'required|integer|min:1',
            'drinks' => 'array',
            'drinks.*.id' => 'integer|nullable',
            'drinks.*.quantity' => 'integer|min:1',
            'drinks.*.price' => 'numeric|min:0',
            'plan.id' => 'required|integer|exists:plans,id',
            'paymentMethod' => 'required|string',
            'user_id' => 'nullable|integer|exists:users,id',
            'totalAmount' => 'required|numeric|min:0',
            'rewardMeal' => 'nullable',
            'freeDrinks' => 'nullable',
            'purchaseType' => 'nullable|string',
            'delivery_slot_ids' => 'required|array|max:3',
            'delivery_slot_ids.*' => 'required|integer|exists:delivery_slots,id|distinct',

        ];
    }

    public function messages(): array
    {
        return [
            'infos.firstName.required' => 'First name is required.',
            'infos.lastName.required' => 'Last name is required.',
            'infos.phoneNumber.required' => 'Phone number is required.',
            'infos.address.required' => 'Address is required.',
            'meals.*.id.exists' => 'Selected meal does not exist.',
            'plan.id.exists' => 'Selected plan does not exist.',
            'paymentMethod.required' => 'Payment method is required.',
            'totalAmount.required' => 'Total amount is required.',
            'totalAmount.numeric' => 'Total amount must be a number.',
            'totalAmount.min' => 'Total amount must be at least 0.',
            'delivery_slot_ids.max' => 'You can select up to 3 delivery slots.',
            'delivery_slot_ids.*.exists' => 'One or more selected delivery slots do not exist.',
            'delivery_slot_ids.*.distinct' => 'You cannot select the same slot multiple times.',
        ];
    }
}
