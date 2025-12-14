<?php

namespace App\Models;

use App\enum\MealCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Meal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'image_path',
        'gallery_images',
        'calories',
        'protein',
        'carbohydrates',
        'fats',
        'fiber',
        'sodium',
        'sugar',
        'ingredients',
        'allergens',
        'preparation_instructions',
        'storage_instructions',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'is_dairy_free',
        'is_nut_free',
        'is_keto',
        'is_paleo',
        'is_low_carb',
        'is_high_protein',
        'is_spicy',
        'spice_level',
        'prep_time_minutes',
        'cooking_time_minutes',
        'difficulty_level',
        'chef_notes',
        'available_from',
        'available_to',
        'is_active',
        'price',
        'cost_per_serving',
        'weight_grams',
        'serving_size',
        'category_id',
        'type_id',
        'is_membership'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'ingredients' => 'array',
        'allergens' => 'array',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_gluten_free' => 'boolean',
        'is_dairy_free' => 'boolean',
        'is_nut_free' => 'boolean',
        'is_keto' => 'boolean',
        'is_paleo' => 'boolean',
        'is_low_carb' => 'boolean',
        'is_high_protein' => 'boolean',
        'is_spicy' => 'boolean',
        'spice_level' => 'integer',
        'prep_time_minutes' => 'integer',
        'cooking_time_minutes' => 'integer',
        'difficulty_level' => 'integer',
        'available_from' => 'date',
        'available_to' => 'date',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'cost_per_serving' => 'decimal:2',
        'weight_grams' => 'integer',
        'calories' => 'integer',
        'protein' => 'decimal:2',
        'carbohydrates' => 'decimal:2',
        'fats' => 'decimal:2',
        'fiber' => 'decimal:2',
        'sodium' => 'decimal:2',
        'sugar' => 'decimal:2',
        'category_id' => 'integer',
        'type_id' => 'integer',
    ];

    // Relationships
    public function weeklyMenus(): BelongsToMany
    {
        return $this->belongsToMany(WeeklyMenu::class, 'menu_meals');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_meals')
            ->withPivot(['quantity', 'meal_price_at_order'])
            ->withTimestamps();
    }

    public function orderMeals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        $today = date('Y-m-d');

        return $query->where(function ($q) use ($today): void {
            $q->where('available_from', '<=', $today)
                ->where('available_to', '>=', $today);
        })->orWhere(function ($q): void {
            $q->whereNull('available_from')
                ->whereNull('available_to');
        });
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }

    public function scopeVegan($query)
    {
        return $query->where('is_vegan', true);
    }

    public function scopeGlutenFree($query)
    {
        return $query->where('is_gluten_free', true);
    }

    public function scopeByType($query, int $type)
    {
        return $query->where('type_id', $type);
    }

    public function scopeMenu($query)
    {
        return $query->where('type_id', 0);
    }

    public function scopeBreakfast($query)
    {
        return $query->where('type_id', 1);
    }

    public function scopeDrinks($query)
    {
        return $query->where('type_id', 2);
    }

    // Helper methods
    public function getNutritionInfo(): array
    {
        return [
            'calories' => $this->calories,
            'protein' => $this->protein . 'g',
            'carbohydrates' => $this->carbohydrates . 'g',
            'fats' => $this->fats . 'g',
            'fiber' => $this->fiber . 'g',
            'sodium' => $this->sodium . 'mg',
            'sugar' => $this->sugar . 'g',
        ];
    }

    public function getDietaryTags(): array
    {
        $tags = [];
        if ($this->is_vegetarian) {
            $tags[] = 'Vegetarian';
        }
        if ($this->is_vegan) {
            $tags[] = 'Vegan';
        }
        if ($this->is_gluten_free) {
            $tags[] = 'Gluten-Free';
        }
        if ($this->is_dairy_free) {
            $tags[] = 'Dairy-Free';
        }
        if ($this->is_nut_free) {
            $tags[] = 'Nut-Free';
        }
        if ($this->is_keto) {
            $tags[] = 'Keto';
        }
        if ($this->is_paleo) {
            $tags[] = 'Paleo';
        }
        if ($this->is_low_carb) {
            $tags[] = 'Low Carb';
        }
        if ($this->is_high_protein) {
            $tags[] = 'High Protein';
        }

        return $tags;
    }

    public function getTotalPrepTime(): int
    {
        return ($this->prep_time_minutes ?? 0) + ($this->cooking_time_minutes ?? 0);
    }

    public function getImageUrl(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return null;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the category label
     */
    public function getCategoryLabel(): ?string
    {
        if ($this->category_id) {
            $category = MealCategory::fromValue($this->category_id);
            return $category?->label();
        }
        return null;
    }

    /**
     * Get the type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->type_id) {
            0 => 'Menu',
            1 => 'Breakfast',
            2 => 'Drinks',
            default => 'Unknown'
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meal): void {
            if (empty($meal->slug)) {
                $meal->slug = Str::slug($meal->name);
            }
        });

        static::updating(function ($meal): void {
            if ($meal->isDirty('name') && empty($meal->slug)) {
                $meal->slug = Str::slug($meal->name);
            }
        });
    }
}
