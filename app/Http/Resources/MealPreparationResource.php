<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MealPreparationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Map order status to preparation status
        $statusMap = [
            'pending' => 'pending',
            'preparing' => 'preparing',
            'ready_for_delivery' => 'ready',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $preparationStatus = $statusMap[$this->order->status->value] ?? 'pending';

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'meal_id' => $this->meal_id,
            'quantity' => $this->quantity,
            'preparation_status' => $preparationStatus,
            'preparation_date' => $this->order->order_date?->format('Y-m-d'),
            'delivery_date' => $this->order->delivery_date?->format('Y-m-d'),
            'notes' => $this->order->notes,
            
            // Customer information
            'customer_name' => $this->order->user?->name,
            
            // Meal details
            'meal' => [
                'id' => $this->meal->id,
                'name' => $this->meal->name,
                'short_description' => $this->meal->short_description,
                'image_path' => $this->meal->image_path ? config('app.url') . Storage::url($this->meal->image_path) : null,
                'price' => $this->meal->price,
            ],
            
            // Order details
            'order' => [
                'id' => $this->order->id,
                'quantity' => $this->quantity,
                'price' => $this->quantity * $this->meal_price_at_order,
                'user' => $this->order->user ? [
                    'id' => $this->order->user->id,
                    'name' => $this->order->user->name,
                    'email' => $this->order->user->email,
                ] : null,
            ],
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
