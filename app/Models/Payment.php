<?php

namespace App\Models;

use App\enum\PaymentMethod;
use App\enum\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'paid_at',
        'payment_gateway',
        'gateway_response',
        'refund_amount',
        'refunded_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'gateway_response' => 'json',
    ];

    /**
     * Get the user that made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get orders that use this payment.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Check if payment succeeded.
     */
    public function isSucceeded(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    /**
     * Mark payment as succeeded.
     */
    public function markAsSucceeded($transactionId = null): bool
    {
        $this->status = PaymentStatus::SUCCEEDED;
        $this->paid_at = now();

        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }

        return $this->save();
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed($reason = null): bool
    {
        $this->status = PaymentStatus::FAILED;

        if ($reason) {
            $this->notes = $reason;
        }

        return $this->save();
    }

    /**
     * Process refund for this payment.
     */
    public function processRefund(float $amount = null): bool
    {
        $refundAmount = $amount ?? $this->amount;

        if ($refundAmount > $this->amount) {
            throw new \InvalidArgumentException('Refund amount cannot exceed original payment amount');
        }

        $this->refund_amount = $refundAmount;
        $this->refunded_at = now();
        $this->status = PaymentStatus::REFUNDED;

        return $this->save();
    }

    /**
     * Get the remaining refundable amount.
     */
    public function getRefundableAmountAttribute(): float
    {
        return $this->amount - ($this->refund_amount ?? 0);
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->isSucceeded() && $this->getRefundableAmountAttribute() > 0;
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Scope for payments by status.
     */
    public function scopeByStatus($query, PaymentStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for successful payments.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    /**
     * Scope for failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    /**
     * Scope for payments by method.
     */
    public function scopeByMethod($query, PaymentMethod $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for payments within date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }
}
