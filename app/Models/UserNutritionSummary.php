<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNutritionSummary extends Model
{
    protected $table = 'user_nutrition_summaries';

    protected $fillable = [
        'user_id',
        'date',
        'calories',
        'fat',
        'protein',
        'carbs',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
