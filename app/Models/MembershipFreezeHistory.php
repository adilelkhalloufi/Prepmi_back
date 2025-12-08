<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipFreezeHistory extends Model
{
    use HasFactory;

    protected $table = 'membership_freeze_history';

    protected $fillable = [
        'membership_id',
        'frozen_at',
        'unfrozen_at',
        'freeze_duration_days',
        'freeze_reason',
        'next_allowed_freeze_date',
    ];

    protected $casts = [
        'frozen_at' => 'date',
        'unfrozen_at' => 'date',
        'freeze_duration_days' => 'integer',
        'next_allowed_freeze_date' => 'date',
    ];

    /**
     * Get the membership that owns the freeze history.
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Check if the freeze is currently active.
     */
    public function isActive(): bool
    {
        return $this->unfrozen_at === null;
    }

    /**
     * Calculate freeze duration if not set.
     */
    public function calculateDuration(): int
    {
        $endDate = $this->unfrozen_at ?? now();
        return $this->frozen_at->diffInDays($endDate);
    }
}
