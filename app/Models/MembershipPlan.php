<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'monthly_fee',
        'discount_percentage',
        'delivery_slots',
        'includes_free_desserts',
        'free_desserts_quantity',
        'perks',
        'is_active',
        'billing_day_of_month',
        'free_delivery',
        'fixed_discount_amount',
        'has_premium_access',
        'premium_upgrade_fee_min',
        'premium_upgrade_fee_max',
        'free_freezes_per_period',
        'freeze_period_months',
        'cancellable_anytime',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'delivery_slots' => 'integer',
        'includes_free_desserts' => 'boolean',
        'free_desserts_quantity' => 'integer',
        'perks' => 'array',
        'is_active' => 'boolean',
        'billing_day_of_month' => 'integer',
        'free_delivery' => 'boolean',
        'fixed_discount_amount' => 'decimal:2',
        'has_premium_access' => 'boolean',
        'premium_upgrade_fee_min' => 'decimal:2',
        'premium_upgrade_fee_max' => 'decimal:2',
        'free_freezes_per_period' => 'integer',
        'freeze_period_months' => 'integer',
        'cancellable_anytime' => 'boolean',
    ];

    /**
     * Get all memberships using this plan.
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get active memberships for this plan.
     */
    public function activeMemberships()
    {
        return $this->hasMany(Membership::class)->where('status', 'active');
    }
}
