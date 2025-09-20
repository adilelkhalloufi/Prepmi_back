<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vegetarian',
        'vegan',
        'gluten_free',
        'dairy_free',
        'nut_free',
        'keto',
        'paleo',
        'low_carb',
        'high_protein',
        'allergies',
        'dislikes',
        'max_calories_per_meal',
        'preferred_portion_size',
        'notes',
    ];

    protected $casts = [
        'vegetarian' => 'boolean',
        'vegan' => 'boolean',
        'gluten_free' => 'boolean',
        'dairy_free' => 'boolean',
        'nut_free' => 'boolean',
        'keto' => 'boolean',
        'paleo' => 'boolean',
        'low_carb' => 'boolean',
        'high_protein' => 'boolean',
        'max_calories_per_meal' => 'integer',
        'allergies' => 'array',
        'dislikes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all dietary restrictions as an array
     */
    public function getDietaryRestrictions(): array
    {
        $restrictions = [];

        if ($this->vegetarian) $restrictions[] = 'Vegetarian';
        if ($this->vegan) $restrictions[] = 'Vegan';
        if ($this->gluten_free) $restrictions[] = 'Gluten-Free';
        if ($this->dairy_free) $restrictions[] = 'Dairy-Free';
        if ($this->nut_free) $restrictions[] = 'Nut-Free';
        if ($this->keto) $restrictions[] = 'Keto';
        if ($this->paleo) $restrictions[] = 'Paleo';
        if ($this->low_carb) $restrictions[] = 'Low Carb';
        if ($this->high_protein) $restrictions[] = 'High Protein';

        return $restrictions;
    }

    /**
     * Check if meal is compatible with user preferences
     */
    public function isCompatibleWithMeal(Meal $meal): bool
    {
        if ($this->vegetarian && !$meal->is_vegetarian) return false;
        if ($this->vegan && !$meal->is_vegan) return false;
        if ($this->gluten_free && !$meal->is_gluten_free) return false;
        if ($this->dairy_free && !$meal->is_dairy_free) return false;
        if ($this->nut_free && !$meal->is_nut_free) return false;

        if ($this->max_calories_per_meal && $meal->calories > $this->max_calories_per_meal) {
            return false;
        }

        return true;
    }
}
