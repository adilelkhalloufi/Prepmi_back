<?php

namespace App\Models;

use App\Enum\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_slot_id',
        'courier_name',
        'tracking_number',
        'delivery_window_start',
        'delivery_window_end',
        'delivered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'delivery_window_start' => 'datetime',
        'delivery_window_end' => 'datetime',
        'delivered_at' => 'datetime',
        'status' => DeliveryStatus::class,
    ];

    /**
     * Get the order associated with this delivery.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the delivery slot associated with this delivery.
     */
    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    /**
     * Check if delivery is pending.
     */
    public function isPending(): bool
    {
        return $this->status === DeliveryStatus::PENDING;
    }

    /**
     * Check if delivery is in transit.
     */
    public function isInTransit(): bool
    {
        return $this->status === DeliveryStatus::IN_TRANSIT;
    }

    /**
     * Check if delivery is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === DeliveryStatus::DELIVERED;
    }

    /**
     * Check if delivery is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === DeliveryStatus::FAILED_DELIVERY;
    }

    /**
     * Mark delivery as delivered.
     */
    public function markAsDelivered(): bool
    {
        $this->status = DeliveryStatus::DELIVERED;
        $this->delivered_at = now();

        return $this->save();
    }

    /**
     * Check if delivery is within delivery window.
     */
    public function isWithinDeliveryWindow(): bool
    {
        $now = now();

        return $now >= $this->delivery_window_start && $now <= $this->delivery_window_end;
    }

    /**
     * Check if delivery window has passed.
     */
    public function isDeliveryWindowPassed(): bool
    {
        return now() > $this->delivery_window_end;
    }

    /**
     * Get delivery window duration in hours.
     */
    public function getDeliveryWindowDurationAttribute(): float
    {
        if (! $this->delivery_window_start || ! $this->delivery_window_end) {
            return 0;
        }

        return $this->delivery_window_start->diffInHours($this->delivery_window_end);
    }

    /**
     * Scope for deliveries by status.
     */
    public function scopeByStatus($query, DeliveryStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for deliveries scheduled for today.
     */
    public function scopeScheduledForToday($query)
    {
        return $query->whereDate('delivery_window_start', today());
    }

    /**
     * Scope for overdue deliveries.
     */
    public function scopeOverdue($query)
    {
        return $query->where('delivery_window_end', '<', now())
            ->whereNotIn('status', [DeliveryStatus::DELIVERED, DeliveryStatus::FAILED_DELIVERY]);
    }
}
