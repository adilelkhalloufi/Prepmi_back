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
            'num_order' => $this->num_order,
            'date_order' => $this->date_order,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'adresse_livrsion' => $this->adresse_livrsion,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'method_payement' => $this->method_payement,
            'reward_point' => $this->reward_point,
            'total_amount' => $this->total_amount,
            'statue' => $this->statue,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'meals' => $this->meals,
            'deliveries' => $this->deliveries ?? [],
        ];
    }
}
