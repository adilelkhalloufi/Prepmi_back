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
