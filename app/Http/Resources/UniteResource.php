<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UniteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = json_decode($this->getRawOriginal('name'), true);

        return [
            'id' => $this->id,
            'name' => $name,
            'created_at' => $this->created_at,
        ];
    }
}
