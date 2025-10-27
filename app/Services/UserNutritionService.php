<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\User;
use App\Models\UserNutritionSummary;

class UserNutritionService
{
    /**
     * Calculate nutrition totals for an order and update the user profile.
     */
    public function updateUserNutritionForOrder(Order $order)
    {
        $user = $order->user;
        if (!$user) return;

        $orderMeals = OrderMeal::with('meal')->where('order_id', $order->id)->get();

        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbohydrates' => 0,
            'fats' => 0,
            'fiber' => 0,
            'sodium' => 0,
            'sugar' => 0,
        ];

        foreach ($orderMeals as $orderMeal) {
            $meal = $orderMeal->meal;
            if ($meal) {
                $qty = $orderMeal->quantity ?? 1;
                $totals['calories'] += ($meal->calories ?? 0) * $qty;
                $totals['protein'] += ($meal->protein ?? 0) * $qty;
                $totals['carbohydrates'] += ($meal->carbohydrates ?? 0) * $qty;
                $totals['fats'] += ($meal->fats ?? 0) * $qty;
                $totals['fiber'] += ($meal->fiber ?? 0) * $qty;
                $totals['sodium'] += ($meal->sodium ?? 0) * $qty;
                $totals['sugar'] += ($meal->sugar ?? 0) * $qty;
            }
        }

        // Update user profile (add fields to User model if needed)
        $user->last_order_calories = $totals['calories'];
        $user->last_order_protein = $totals['protein'];
        $user->last_order_carbohydrates = $totals['carbohydrates'];
        $user->last_order_fats = $totals['fats'];
        $user->last_order_fiber = $totals['fiber'];
        $user->last_order_sodium = $totals['sodium'];
        $user->last_order_sugar = $totals['sugar'];
        $user->save();
    }

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
