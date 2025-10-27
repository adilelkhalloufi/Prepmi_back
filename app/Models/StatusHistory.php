<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
        'notes',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the model that owns the status history.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who changed the status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the order if the model is an order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'model_id');
    }
}
