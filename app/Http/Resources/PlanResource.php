<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'meals_per_week' => $this->meals_per_week,
            'price_per_week' => $this->price_per_week,
            'delivery_fee' => $this->delivery_fee,
            'is_active' => $this->is_active,
            'is_free_shipping' => $this->is_free_shipping,
            'is_featured' => $this->is_featured,
            'points_value' => $this->points_value,
            'sort_order' => $this->sort_order,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
