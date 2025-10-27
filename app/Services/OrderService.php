<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Add reward points to a user. If total_points_earned > 12, reset to 0 and create a reward.
     */
    public function addRewardPointsToUser($user, $points = 1)
    {
        $user->total_points_earned += $points;
            if ($user->total_points_earned >= 12) {
            $user->total_points_earned = $user->total_points_earned - 12;
            // Create a reward for the user
                     \App\Models\Reward::create([
                        'user_id' => $user->id,
                        'type' => 'free_meal',
                        'value' => 49.00,
                        'title' => 'Repas PrepMe Gratuit',
                        'description' => 'Réduction de 49 MAD applicable sur votre prochaine commande',
                        'earned_at' => now(),
                        'is_used' => false,
                    ]);
                
        }
        $user->save();
    }
    public function createOrderWithRewards(array $data): Order
    {
        $infos = $data['infos'] ?? [];
        $planId = $data['plan']['id'] ?? null;
        $meals = $data['meals'] ?? [];
        $drinks = $data['drinks'] ?? [];
        $paymentMethod = $data['paymentMethod'] ?? null;
        $userId = Auth::id() ?? $data['user_id'] ?? null;
        $totalAmount = $data['totalAmount'] ?? 0;
        $plan = $planId ? Plan::find($planId) : null;
        $rewardPoints = $plan ? ($plan->points_value ?? 0) : 0;



        // i want log the total amount
        // \Log::info('Total amount calculated: ' . $totalAmount);
        // Generate num_order: ORD-YYYYMMDD-XXXX (increment for the day)
        $today = now()->format('Ymd');
        $orderCountToday = \App\Models\Order::whereDate('created_at', now()->toDateString())->count();
        $numOrder = 'ORD-' . $today . '-' . str_pad($orderCountToday + 1, 4, '0', STR_PAD_LEFT);

        $order = Order::create([
            'num_order' => $numOrder,
            'first_name' => $infos['firstName'] ?? null,
            'last_name' => $infos['lastName'] ?? null,
            'phone' => $infos['phoneNumber'] ?? null,
            'adresse_livrsion' => $infos['address'] ?? null,
            'plan_id' => $planId,
            'method_payement' => $paymentMethod,
            'user_id' => $userId,
            'date_order' => now(),
            'statue' => 'pending',
            'reward_point' => $rewardPoints,
            'total_amount' => $totalAmount,
        ]);

        // Attach meals
        foreach ($meals as $meal) {
            $order->meals()->attach($meal['id'], [
                'quantity' => $meal['quantity'],
                'plan_id' => $planId,
                'price' => $plan->price ?? 0,
            ]);
        }
        // Attach drinks as meals
        foreach ($drinks as $drink) {
            $order->meals()->attach($drink['id'], [
                'quantity' => $drink['quantity'],
                'plan_id' => $planId,
                'price' => $drink['price'] ?? 0,
            ]);
        }
        // Add reward points to user
        $user = \App\Models\User::find($userId);
        if ($user) {
            $this->addRewardPointsToUser($user, $rewardPoints);
        }
        return $order;
    }
}
