<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_menu_id',
        'meal_id',
        'position',
        'is_featured',
        'special_price',
        'availability_count',
        'sold_count',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_featured' => 'boolean',
        'special_price' => 'decimal:2',
        'availability_count' => 'integer',
        'sold_count' => 'integer',
    ];

    // Relationships
    public function weeklyMenu(): BelongsTo
    {
        return $this->belongsTo(WeeklyMenu::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    // Helper methods
    public function getEffectivePrice(): float
    {
        return $this->special_price ?? $this->meal->price;
    }

    public function isAvailable(): bool
    {
        if ($this->availability_count === null) {
            return true; // No limit set
        }

        return ($this->sold_count ?? 0) < $this->availability_count;
    }

    public function getRemainingCount(): ?int
    {
        if ($this->availability_count === null) {
            return null; // No limit
        }

        return max(0, $this->availability_count - ($this->sold_count ?? 0));
    }

    public function incrementSoldCount(int $quantity = 1): void
    {
        $this->increment('sold_count', $quantity);
    }

    public function decrementSoldCount(int $quantity = 1): void
    {
        $this->decrement('sold_count', $quantity);
    }
}
