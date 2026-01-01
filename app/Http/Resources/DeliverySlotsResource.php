<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverySlotsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot_name' => $this->slot_name,
            'slot_type' => $this->slot_type?->value,
            'slot_type_label' => $this->slot_type?->getLabel(),
            'start_time' => $this->start_time ? \Carbon\Carbon::parse($this->start_time)->format('H:i') : null,
            'end_time' => $this->end_time ? \Carbon\Carbon::parse($this->end_time)->format('H:i') : null,
            'max_capacity' => $this->max_capacity,
            'current_bookings' => $this->current_bookings,
            'remaining_capacity' => $this->getRemainingCapacity(),
            'day_of_week' => $this->day_of_week,
            'day_of_week_name' => $this->day_of_week !== null ? ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$this->day_of_week] : 'All Days',
            'is_active' => $this->is_active,
            'is_available' => $this->isAvailable(),
            'is_full' => $this->isFull(),
            'price_adjustment' => (float) $this->price_adjustment,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
