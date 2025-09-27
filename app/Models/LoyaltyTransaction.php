<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'description',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeemed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isEarned(): bool
    {
        return $this->type === 'earned';
    }

    public function isRedeemed(): bool
    {
        return $this->type === 'redeemed';
    }
}
