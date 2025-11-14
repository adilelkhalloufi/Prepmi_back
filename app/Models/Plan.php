<?php

namespace App\Models;

use App\Enum\DeliveryFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'meals_per_week',
        'price_per_week',
        'is_active',
        'points_value',
        'delivery_fee',
        'is_free_shipping',
        'price_subscription_per_week',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free_shipping' => 'boolean',
        'price_per_week' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'points_value' => 'integer',
        'meals_per_week' => 'integer',

    ];

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function getWeeklyPrice(): float
    {
        return $this->price_per_meal * $this->meals_per_week;
    }

    public function getMonthlyPrice(): float
    {
        $weeksPerMonth = $this->delivery_frequency->getDaysInterval() === 7 ? 4 : ($this->delivery_frequency->getDaysInterval() === 14 ? 2 : 1);

        return $this->getWeeklyPrice() * $weeksPerMonth;
    }

    public function getTotalDeliveryPrice(): float
    {
        $basePrice = $this->getWeeklyPrice();

        if (! $this->is_free_shipping) {
            $basePrice += $this->delivery_fee;
        }

        return $basePrice;
    }

    public function getDiscountedPrice(): float
    {
        $basePrice = $this->getWeeklyPrice();

        if ($this->discount_percentage > 0) {
            $discount = $basePrice * ($this->discount_percentage / 100);
            $basePrice -= $discount;
        }

        return $basePrice;
    }

    public function isTrialEligible(): bool
    {
        return $this->is_trial_available && $this->trial_period_days > 0;
    }

    public function hasCommitment(): bool
    {
        return $this->min_weeks_commitment > 0;
    }

    public function getCommitmentLabel(): string
    {
        if (! $this->hasCommitment()) {
            return 'No commitment';
        }

        $min = $this->min_weeks_commitment;
        $max = $this->max_weeks_commitment;

        if ($max && $max > $min) {
            return "{$min}-{$max} weeks minimum";
        }

        return "{$min} weeks minimum";
    }

    public function calculatePriceForWeeks(int $weeks): float
    {
        $weeklyPrice = $this->getTotalDeliveryPrice();
        $totalPrice = $weeklyPrice * $weeks;

        if ($this->setup_fee > 0) {
            $totalPrice += $this->setup_fee;
        }

        return $totalPrice;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
