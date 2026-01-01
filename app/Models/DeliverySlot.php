<?php

namespace App\Models;

use App\enum\SlotType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_name',
        'slot_type',
        'start_time',
        'end_time',
        'max_capacity',
        'current_bookings',
        'day_of_week',
        'is_active',
        'price_adjustment',
        'description',
    ];

    protected $casts = [
        'slot_type' => SlotType::class,
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'max_capacity' => 'integer',
        'current_bookings' => 'integer',
        'is_active' => 'boolean',
        'price_adjustment' => 'decimal:2',
    ];

    /**
     * Get the deliveries for this slot.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_slot_id');
    }

    /**
     * Check if slot is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->current_bookings < $this->max_capacity;
    }

    /**
     * Check if slot is available for membership users.
     */
    public function isAvailableForMembership(): bool
    {
        return $this->isAvailable() &&
            in_array($this->slot_type, [SlotType::MEMBERSHIP, SlotType::BOTH]);
    }

    /**
     * Check if slot is available for normal users.
     */
    public function isAvailableForNormalUser(): bool
    {
        return $this->isAvailable() &&
            in_array($this->slot_type, [SlotType::NORMAL, SlotType::BOTH]);
    }

    /**
     * Check if slot is full.
     */
    public function isFull(): bool
    {
        return $this->current_bookings >= $this->max_capacity;
    }

    /**
     * Get remaining capacity.
     */
    public function getRemainingCapacity(): int
    {
        return max(0, $this->max_capacity - $this->current_bookings);
    }

    /**
     * Book a slot.
     */
    public function book(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->increment('current_bookings');
        return true;
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(): bool
    {
        if ($this->current_bookings <= 0) {
            return false;
        }

        $this->decrement('current_bookings');
        return true;
    }

    /**
     * Scope query to only active slots.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only membership slots.
     */
    public function scopeMembershipOnly($query)
    {
        return $query->where('slot_type', SlotType::MEMBERSHIP);
    }

    /**
     * Scope query to only normal user slots.
     */
    public function scopeNormalOnly($query)
    {
        return $query->where('slot_type', SlotType::NORMAL);
    }

    /**
     * Scope query to slots available for membership.
     */
    public function scopeForMembership($query)
    {
        return $query->whereIn('slot_type', [SlotType::MEMBERSHIP, SlotType::BOTH]);
    }

    /**
     * Scope query to slots available for normal users.
     */
    public function scopeForNormalUsers($query)
    {
        return $query->whereIn('slot_type', [SlotType::NORMAL, SlotType::BOTH]);
    }

    /**
     * Scope query to available slots.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->whereColumn('current_bookings', '<', 'max_capacity');
    }

    /**
     * Scope query by day of week.
     */
    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }
}
