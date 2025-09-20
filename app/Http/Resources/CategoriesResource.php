<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
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
            'family_id' => $this->family_id,
            'subcategories' => CategoriesResource::collection($this->sub_categories),
        ];
    }
}
