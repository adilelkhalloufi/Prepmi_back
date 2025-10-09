<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'title',
        'description',
        'is_used',
        'earned_at',
        'expires_at',
        'used_at',
        'used_order_id',
        'discount_applied',
        'conditions',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_used' => 'boolean',
        'earned_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'discount_applied' => 'decimal:2',
        'conditions' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_order_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
            ->where('is_used', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return ! $this->is_used &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null &&
            $this->expires_at->isPast() &&
            ! $this->is_used;
    }

    public function markAsUsed(Order $order, ?float $discountApplied = null): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_order_id' => $order->id,
            'discount_applied' => $discountApplied ?? $this->value,
        ]);
    }
}
