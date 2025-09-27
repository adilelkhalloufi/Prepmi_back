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
        'slug',
        'description',
        'short_description',
        'price_per_meal',
        'meals_per_week',
        'delivery_frequency',
        'min_weeks_commitment',
        'max_weeks_commitment',
        'setup_fee',
        'delivery_fee',
        'is_free_shipping',
        'trial_period_days',
        'is_trial_available',
        'discount_percentage',
        'features',
        'restrictions',
        'is_active',
        'is_featured',
        'sort_order',
        'terms_and_conditions',
        'points_value',
        'box_price',
    ];

    protected $casts = [
        'price_per_meal' => 'decimal:2',
        'meals_per_week' => 'integer',
        'delivery_frequency' => DeliveryFrequency::class,
        'min_weeks_commitment' => 'integer',
        'max_weeks_commitment' => 'integer',
        'setup_fee' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'is_free_shipping' => 'boolean',
        'trial_period_days' => 'integer',
        'is_trial_available' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'features' => 'array',
        'restrictions' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'points_value' => 'integer',
        'box_price' => 'decimal:2',
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

        if (!$this->is_free_shipping) {
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
        if (!$this->hasCommitment()) {
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
