<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'user_id',
        'amount',
        'transaction_type',
        'payment_status',
        'payment_method',
        'payment_reference',
        'billing_period_start',
        'billing_period_end',
        'charged_at',
        'failed_at',
        'failure_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'charged_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the membership that owns the transaction.
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if transaction failed.
     */
    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }
}
