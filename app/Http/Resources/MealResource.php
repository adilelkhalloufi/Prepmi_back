<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MealResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'is_membership' => $this->is_membership,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path ? config('app.url') . Storage::disk('public_uploads')->url($this->image_path) : null,
            'gallery_images' => $this->gallery_images,
            'gallery_urls' => $this->gallery_images ? array_map(fn($path) => config('app.url') . Storage::disk('public_uploads')->url($path), $this->gallery_images) : [],
            'price' => $this->price,
            'weight_grams' => $this->weight_grams,
            'serving_size' => $this->serving_size,

            // Nutritional Information
            'nutrition' => [
                'calories' => $this->calories,
                'protein' => $this->protein,
                'carbohydrates' => $this->carbohydrates,
                'fats' => $this->fats,
                'fiber' => $this->fiber,
                'sodium' => $this->sodium,
                'sugar' => $this->sugar,
            ],

            // Ingredients and Allergens
            'ingredients' => is_array($this->ingredients)
                ? $this->ingredients
                : (is_string($this->ingredients) && trim($this->ingredients) !== ''
                    ? array_map('trim', explode(',', $this->ingredients))
                    : []),
            'allergens' => $this->allergens,

            // Dietary Preferences
            'dietary_info' => [
                'is_vegetarian' => $this->is_vegetarian,
                'is_vegan' => $this->is_vegan,
                'is_gluten_free' => $this->is_gluten_free,
                'is_dairy_free' => $this->is_dairy_free,
                'is_nut_free' => $this->is_nut_free,
                'is_keto' => $this->is_keto,
                'is_paleo' => $this->is_paleo,
                'is_low_carb' => $this->is_low_carb,
                'is_high_protein' => $this->is_high_protein,
            ],

            // Preparation Details
            'preparation' => [
                'prep_time_minutes' => $this->prep_time_minutes,
                'cooking_time_minutes' => $this->cooking_time_minutes,
                'total_time_minutes' => $this->prep_time_minutes + $this->cooking_time_minutes,
                'difficulty_level' => $this->difficulty_level,
                'instructions' => $this->preparation_instructions,
                'storage_instructions' => $this->storage_instructions,
            ],

            // Spice Information
            'is_spicy' => $this->is_spicy,
            'spice_level' => $this->spice_level,

            // Chef Notes
            'chef_notes' => $this->chef_notes,

            // Availability
            'available_from' => $this->available_from,
            'available_to' => $this->available_to,
            'is_active' => $this->is_active,

            // Cost Information
            'cost_per_serving' => $this->cost_per_serving,
            'category_id' => $this->category_id,
            'category' => $this->category,

            // Type Information
            'type_id' => $this->type_id,
            'type' => $this->getTypeLabel(),

            'quantity' => $this->quantity ?? 0,

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
