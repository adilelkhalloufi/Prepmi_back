<?php

namespace App\Models;

use App\Enum\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'started_at',
        'ends_at',
        'next_billing_date',
        'next_delivery_date',
        'paused_at',
        'pause_reason',
        'cancelled_at',
        'cancellation_reason',
        'weeks_committed',
        'weeks_remaining',
        'total_amount_paid',
        'meals_delivered',
        'delivery_address',
        'delivery_notes',
        'special_instructions',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_billing_date' => 'date',
        'next_delivery_date' => 'date',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'weeks_committed' => 'integer',
        'weeks_remaining' => 'integer',
        'total_amount_paid' => 'decimal:2',
        'meals_delivered' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

 

 

 
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    public function scopePaused($query)
    {
        return $query->where('status', SubscriptionStatus::PAUSED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', SubscriptionStatus::CANCELLED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('next_billing_date', '<=', now()->toDateString());
    }

    public function scopeDueForDelivery($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('next_delivery_date', '<=', now()->toDateString());
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === SubscriptionStatus::PAUSED;
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED;
    }

    public function pause(?string $reason = null): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $this->update([
            'status' => SubscriptionStatus::PAUSED,
            'paused_at' => now(),
            'pause_reason' => $reason,
        ]);

        return true;
    }

    public function resume(): bool
    {
        if (! $this->isPaused()) {
            return false;
        }

        // Calculate new billing and delivery dates
        $pauseDurationDays = $this->paused_at->diffInDays(now());

        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'paused_at' => null,
            'pause_reason' => null,
            'next_billing_date' => $this->next_billing_date->addDays($pauseDurationDays),
            'next_delivery_date' => $this->next_delivery_date->addDays($pauseDurationDays),
        ]);

        return true;
    }

    public function cancel(?string $reason = null): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        $this->update([
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    public function renew(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Calculate next billing and delivery dates
        $frequency = $this->plan->delivery_frequency;
        $intervalDays = $frequency->getDaysInterval();

        $this->update([
            'next_billing_date' => $this->next_billing_date->addDays($intervalDays),
            'next_delivery_date' => $this->next_delivery_date->addDays($intervalDays),
        ]);

        // Decrement remaining weeks if there's a commitment
        if ($this->weeks_remaining > 0) {
            $weeksToDecrement = $intervalDays === 7 ? 1 : ($intervalDays === 14 ? 2 : 4);
            $this->decrement('weeks_remaining', $weeksToDecrement);

            // Check if commitment is complete
            if ($this->weeks_remaining <= 0) {
                $this->update(['weeks_remaining' => 0]);
            }
        }

        return true;
    }

    public function getWeeklyPrice(): float
    {
        return $this->plan->getTotalDeliveryPrice();
    }

    public function getRemainingCommitmentWeeks(): int
    {
        return max(0, $this->weeks_remaining ?? 0);
    }

    public function hasCommitmentRemaining(): bool
    {
        return $this->getRemainingCommitmentWeeks() > 0;
    }

    public function getNextBillingAmount(): float
    {
        return $this->getWeeklyPrice();
    }

    public function incrementAmountPaid(float $amount): void
    {
        $this->increment('total_amount_paid', $amount);
    }

    public function incrementMealsDelivered(int $count = 1): void
    {
        $this->increment('meals_delivered', $count);
    }
}
