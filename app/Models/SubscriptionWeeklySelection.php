<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionWeeklySelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'weekly_menu_id',
        'week_start_date',
        'week_end_date',
        'week_number',
        'status',
        'locked_at',
        'confirmed_at',
        'scheduled_delivery_date',
        'delivered_at',
        'delivery_notes',
        'special_instructions',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'week_number' => 'integer',
        'locked_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'scheduled_delivery_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function weeklyMenu(): BelongsTo
    {
        return $this->belongsTo(WeeklyMenu::class);
    }

    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'subscription_weekly_selection_meals')
            ->withPivot(['quantity', 'position', 'price_at_selection', 'selected_at', 'modified_at'])
            ->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeForWeek($query, $startDate)
    {
        return $query->where('week_start_date', $startDate);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('week_start_date', '>', now()->toDateString())
            ->orderBy('week_start_date');
    }

    public function scopeDueForDelivery($query)
    {
        return $query->where('status', '!=', 'delivered')
            ->where('scheduled_delivery_date', '<=', now()->toDateString());
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function canModify(): bool
    {
        // Can only modify if not locked or delivered
        return !in_array($this->status, ['locked', 'delivered']);
    }

    public function lockSelection(): void
    {
        $this->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);
    }

    public function confirmSelection(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function getTotalMealsSelected(): int
    {
        return $this->meals()->sum('quantity');
    }

    public function isSelectionComplete(): bool
    {
        $plan = $this->subscription->plan;
        $totalSelected = $this->getTotalMealsSelected();
        
        return $totalSelected === $plan->meals_per_week;
    }
}
