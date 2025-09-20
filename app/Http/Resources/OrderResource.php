<?php

namespace App\Http\Resources;

use App\enum\EnumOrderStatue;
use App\enum\EnumPayement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'note' => $this->note,
            'product' => ProductRessource::make($this->product),
            'payment' => EnumPayement::from($this->payment)->getLabel(),
            'status' => EnumOrderStatue::from($this->status)->getLabel(),
    
        ];
    }
}
