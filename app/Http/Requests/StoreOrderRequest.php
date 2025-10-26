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
        ];
    }
}
