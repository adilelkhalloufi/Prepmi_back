<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\User;
use App\Models\UserNutritionSummary;

class UserNutritionService
{
    

    /**
     * Calculate and store daily nutrition summary for a user.
     */
    public function updateDailySummary($userId, $date)
    {
        $orders = Order::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->pluck('id');

        $orderMeals = OrderMeal::with('meal')
            ->whereIn('order_id', $orders)
            ->get();

        $totals = [
            'calories' => 0,
            'fat' => 0,
            'protein' => 0,
            'carbs' => 0,
        ];

        foreach ($orderMeals as $orderMeal) {
            $meal = $orderMeal->meal;
            if ($meal) {
                $qty = $orderMeal->quantity ?? 1;
                $totals['calories'] += ($meal->calories ?? 0) * $qty;
                $totals['fat'] += ($meal->fats ?? 0) * $qty;
                $totals['protein'] += ($meal->protein ?? 0) * $qty;
                $totals['carbs'] += ($meal->carbohydrates ?? 0) * $qty;
            }
        }

        UserNutritionSummary::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            [
                'calories' => $totals['calories'],
                'fat' => $totals['fat'],
                'protein' => $totals['protein'],
                'carbs' => $totals['carbs'],
            ]
        );
    }
}
