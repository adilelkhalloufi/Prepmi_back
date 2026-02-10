<?php

namespace App\Models;

use App\Enum\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'status',
        'started_at',
        'ends_at',
        'next_billing_date',
        'current_monthly_fee',
        'discount_percentage',
        'delivery_slots_available',
        'has_received_monthly_desserts',
        'last_desserts_received_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'status' => MembershipStatus::class,
        'started_at' => 'date',
        'ends_at' => 'date',
        'next_billing_date' => 'date',
        'current_monthly_fee' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'delivery_slots_available' => 'integer',
        'has_received_monthly_desserts' => 'boolean',
        'last_desserts_received_at' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the membership.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the membership plan.
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    /**
     * Get the freeze history for the membership.
     */
    public function freezeHistory()
    {
        return $this->hasMany(MembershipFreezeHistory::class);
    }

    /**
     * Get the transactions for the membership.
     */
    public function transactions()
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    /**
     * Get the perks usage for the membership.
     */
    public function perksUsage()
    {
        return $this->hasMany(MembershipPerkUsage::class);
    }

    /**
     * Check if membership is active.
     */
    public function isActive(): bool
    {
        return $this->status === MembershipStatus::ACTIVE;
    }

    /**
     * Check if membership is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->status === MembershipStatus::FROZEN;
    }

    /**
     * Check if membership can be frozen (not frozen in last 6 months).
     */
    public function canFreeze(): bool
    {
        $lastFreeze = $this->freezeHistory()
            ->orderBy('frozen_at', 'desc')
            ->first();

        if (!$lastFreeze || !$lastFreeze->next_allowed_freeze_date) {
            return true;
        }

        return now()->gte($lastFreeze->next_allowed_freeze_date);
    }
}
