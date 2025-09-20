<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeal extends Model
{
    use HasFactory;

    protected $table = 'order_meals';

    protected $fillable = [
        'order_id',
        'meal_id',
        'quantity',
        'meal_price_at_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'meal_price_at_order' => 'decimal:2',
    ];

    /**
     * Get the order that owns this order meal.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the meal associated with this order meal.
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Get the subtotal for this order meal item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->meal_price_at_order;
    }

    /**
     * Scope to filter by meal.
     */
    public function scopeForMeal($query, $mealId)
    {
        return $query->where('meal_id', $mealId);
    }

    /**
     * Scope to filter by order.
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
