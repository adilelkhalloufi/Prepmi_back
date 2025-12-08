<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPerkUsage extends Model
{
    use HasFactory;

    protected $table = 'membership_perks_usage';

    protected $fillable = [
        'membership_id',
        'order_id',
        'perk_type',
        'discount_amount',
        'items_quantity',
        'used_at',
        'perk_details',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'items_quantity' => 'integer',
        'used_at' => 'date',
        'perk_details' => 'array',
    ];

    /**
     * Get the membership that owns the perk usage.
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Get the order associated with the perk usage.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if perk is a discount.
     */
    public function isDiscount(): bool
    {
        return $this->perk_type === 'discount_applied';
    }

    /**
     * Check if perk is free desserts.
     */
    public function isFreeDesserts(): bool
    {
        return $this->perk_type === 'free_desserts';
    }

    /**
     * Check if perk is delivery slot.
     */
    public function isDeliverySlot(): bool
    {
        return $this->perk_type === 'delivery_slot_used';
    }
}
