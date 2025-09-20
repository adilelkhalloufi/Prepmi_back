<?php

namespace App\Http\Resources;

use App\enum\ProductAdminStatus;
use App\enum\ProductStatue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statue = json_decode(ProductStatue::from($this->status)->getLabel(), true);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'user' => UserResource::make($this->user),
            'categorie' => CategoriesResource::make($this->categorie),
            'unite' => UniteResource::make($this->unite),
            'status' => [
                'name' => $statue,
                'color' => ProductStatue::from($this->status)->getColor(),
            ],
            'status_id' => $this->status,
            'image' => $this->image,
            'quantity' => $this->quantity,
            'availability_status' => ProductAdminStatus::from($this->availability_status)->getLabel(),
            'auction' => $this->auction == 1 ? true : false,
            'date_end_auction' => $this->date_end_auction,
            'conditions_document' => $this->conditions_document,
            'conditions_document_price' => $this->conditions_document_price,
            'show_company' => $this->show_company == 1 ? true : false,
            'favaris' => $this->favaris == 1 ? true : false,
            'created_at' => $this->created_at,
            'relatedProducts' => $this->relatedProducts ? ProductRessource::collection($this->relatedProducts) : [],
        ];
    }
}
