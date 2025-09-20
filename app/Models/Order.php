<?php

namespace App\Models;

use App\enum\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'order_date',
        'delivery_date',
        'status',
        'total_price',
        'address_id',
        'payment_id',
        'notes',
        'delivery_instructions',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'status' => OrderStatus::class,
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the user that placed this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with this order (if any).
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the delivery address for this order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the payment for this order.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the delivery information for this order.
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * Get all meals in this order with pivot data.
     */
    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'order_meals')
            ->withPivot(['quantity', 'meal_price_at_order'])
            ->withTimestamps();
    }

    /**
     * Get the order meals records.
     */
    public function orderMeals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    /**
     * Check if order is being prepared.
     */
    public function isPreparing(): bool
    {
        return $this->status === OrderStatus::PREPARING;
    }

    /**
     * Check if order is shipped.
     */
    public function isShipped(): bool
    {
        return $this->status === OrderStatus::SHIPPED;
    }

    /**
     * Check if order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    /**
     * Get the total number of meals in this order.
     */
    public function getTotalMealsCountAttribute(): int
    {
        return $this->orderMeals()->sum('quantity');
    }

    /**
     * Calculate total price from order meals.
     */
    public function calculateTotalPrice(): float
    {
        return $this->orderMeals()->get()
            ->sum(function ($orderMeal) {
                return $orderMeal->quantity * $orderMeal->meal_price_at_order;
            });
    }

    /**
     * Update order status and save.
     */
    public function updateStatus(OrderStatus $status): bool
    {
        $this->status = $status;
        return $this->save();
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [OrderStatus::PENDING, OrderStatus::PREPARING]);
    }

    /**
     * Check if order can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    /**
     * Scope for orders within date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Scope for orders by status.
     */
    public function scopeByStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for subscription orders.
     */
    public function scopeSubscriptionOrders($query)
    {
        return $query->whereNotNull('subscription_id');
    }

    /**
     * Scope for one-time orders.
     */
    public function scopeOneTimeOrders($query)
    {
        return $query->whereNull('subscription_id');
    }
}
