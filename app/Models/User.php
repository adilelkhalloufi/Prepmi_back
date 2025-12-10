<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'loyalty_points',
        'total_points_earned',
        'total_points_redeemed',
        'total_rewards_earned',
        'total_rewards_used',
        'last_reward_earned_at',
        'badges',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'badges' => 'array',
        'last_reward_earned_at' => 'datetime',
    ];

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function getFilamentName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Relationships order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function userNutritionSummaries()
    {
        return $this->hasMany(UserNutritionSummary::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
