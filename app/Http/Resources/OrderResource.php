<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'subscription_id' => $this->subscription_id,
            'subscription' => $this->subscription ? [
                'id' => $this->subscription->id,
                'plan_name' => $this->subscription->plan_name ?? null,
            ] : null,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'status' => $this->status,
            'total_price' => (float) $this->total_price,
            'address' => [
                'id' => $this->address->id,
                'full_address' => $this->address->full_address ?? null,
            ],
            'notes' => $this->notes,
            'delivery_instructions' => $this->delivery_instructions,
            'meals' => $this->meals->map(fn($meal) => [
                'id' => $meal->id,
                'name' => $meal->name,
                'quantity' => $meal->pivot->quantity,
                'price_at_order' => (float) $meal->pivot->meal_price_at_order,
                'subtotal' => (float) ($meal->pivot->quantity * $meal->pivot->meal_price_at_order),
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
