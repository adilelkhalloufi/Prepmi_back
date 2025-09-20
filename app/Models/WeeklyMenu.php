<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WeeklyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start_date',
        'week_end_date',
        'title',
        'description',
        'is_active',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($menu) {
            if (empty($menu->week_end_date) && $menu->week_start_date) {
                $menu->week_end_date = Carbon::parse($menu->week_start_date)->addDays(6);
            }
        });
    }

    // Relationships
    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'menu_meals')
            ->withPivot(['position', 'is_featured', 'special_price'])
            ->withTimestamps();
    }

    public function menuMeals(): HasMany
    {
        return $this->hasMany(MenuMeal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('week_start_date', '<=', $today)
            ->where('week_end_date', '>=', $today);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('week_start_date', '>', now()->toDateString())
            ->orderBy('week_start_date');
    }

    public function scopeForWeek($query, $date)
    {
        $startOfWeek = Carbon::parse($date)->startOfWeek();
        $endOfWeek = Carbon::parse($date)->endOfWeek();

        return $query->where('week_start_date', '<=', $endOfWeek->toDateString())
            ->where('week_end_date', '>=', $startOfWeek->toDateString());
    }

    // Helper methods
    public function getWeekLabel(): string
    {
        $start = Carbon::parse($this->week_start_date);
        $end = Carbon::parse($this->week_end_date);

        return $start->format('M j') . ' - ' . $end->format('M j, Y');
    }

    public function isCurrent(): bool
    {
        $today = now()->toDate();
        return $this->week_start_date <= $today && $this->week_end_date >= $today;
    }

    public function isUpcoming(): bool
    {
        return $this->week_start_date > now()->toDate();
    }

    public function isPast(): bool
    {
        return $this->week_end_date < now()->toDate();
    }

    public function getMealCount(): int
    {
        return $this->meals()->count();
    }

    public function getFeaturedMeals()
    {
        return $this->meals()->wherePivot('is_featured', true);
    }

    public function addMeal(Meal $meal, array $pivotData = []): void
    {
        $defaultPivotData = [
            'position' => $this->getMealCount() + 1,
            'is_featured' => false,
            'special_price' => null,
        ];

        $this->meals()->attach($meal->id, array_merge($defaultPivotData, $pivotData));
    }

    public function removeMeal(Meal $meal): void
    {
        $this->meals()->detach($meal->id);
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
