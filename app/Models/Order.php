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
        'num_order',
        'date_order',
        'first_name',
        'last_name',
        'phone',
        'adresse_livrsion',
        'user_id',
        'plan_id',
        'method_payement',
        'reward_point',
        'statue',
        'total_amount',
        'subscription_id'
    ];

    protected $casts = [
        'date_order' => 'datetime',
        'reward_point' => 'integer',
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
     * Get all deliveries for this order (supports multiple delivery slots).
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Get the loyalty transactions for this order.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Get the reward used for this order.
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Get all meals in this order.
     */
    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'order_meals')
            ->withPivot(['quantity', 'price', 'plan_id'])
            ->withTimestamps();
    }

    /**
     * Get the order meals records.
     */
    public function orderMeals(): HasMany
    {
        return $this->hasMany(OrderMeal::class);
    }


    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }



    /**
     * Get the status histories for this order.
     */

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }
}
