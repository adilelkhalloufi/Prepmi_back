<?php

namespace App\Services;

use App\Enum\OrderStatus;
use App\enum\UserRole;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Attach reward meal to order and mark the reward as used.
     */
    public function attachRewardMealToOrder(Order $order, array $rewardMeal, $planId)
    {

        $mealId = $rewardMeal['mealId'] ?? null;
        $rewardId = $rewardMeal['rewardId'] ?? null;
        $quantity = $rewardMeal['quantity'] ?? 1;


        if ($mealId) {
            // Attach meal to order with details
            $order->meals()->attach($mealId, [
                'quantity' => $quantity,
                'plan_id' => $planId,
                'price' => 0, // Reward meal is free
                'is_reward_meal' => true,
            ]);
        }

        // Mark the reward as used
        if ($rewardId) {
            $reward = \App\Models\Reward::find($rewardId);
            if ($reward) {
                $reward->update([
                    'is_used' => true,
                    'used_at' => now(),
                ]);
            }
        }
    }

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

    /**
     * Create a user if email and password are provided and user doesn't exist.
     */
    public function createUserIfNotExists($email, $password, $additionalData = [])
    {
        if (!$email || !$password) {
            return null;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }

        $userData = array_merge([
            'email' => $email,
            'password' => Hash::make($password),
            'first_name' => $additionalData['first_name'] ?? null,
            'last_name' => $additionalData['last_name'] ?? null,
            'phone' => $additionalData['phone'] ?? null,
            'address' => $additionalData['address'] ?? null,
            'role' => UserRole::CLIENT->value,

        ], $additionalData);

        return User::create($userData);
    }

    public function createOrderWithRewards(array $data): Order
    {

        $infos = $data['infos'] ?? [];
        $planId = $data['plan']['id'] ?? null;
        $meals = $data['meals'] ?? [];
        $drinks = $data['drinks'] ?? [];
        $rewardMeal = $data['rewardMeal'] ?? null;
        $paymentMethod = $data['paymentMethod'] ?? null;
        $userId = Auth::id() ?? $data['user_id'] ?? null;
        $totalAmount = $data['totalAmount'] ?? 0;

        Log::info('rewardMeal extracted:', ['rewardMeal' => $rewardMeal]);

        if (isset($infos['email']) && isset($infos['password'])) {
            $user = $this->createUserIfNotExists($infos['email'], $infos['password'], [
                'first_name' => $infos['firstName'] ?? null,
                'last_name' => $infos['lastName'] ?? null,
                'phone' => $infos['phoneNumber'] ?? null,
                'address' => $infos['address'] ?? null,
            ]);
            $userId = $user ? $user->id : null;
        }

        $plan = $planId ? Plan::find($planId) : null;
        $rewardPoints = $plan ? ($plan->points_value ?? 0) : 0;



        // i want log the total amount
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
            'statue' => OrderStatus::PENDING->value,
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

        // Handle reward meal if provided
        if ($rewardMeal) {
            $this->attachRewardMealToOrder($order, $rewardMeal, $planId);
        }

        // Add reward points to user
        $user = \App\Models\User::find($userId);
        if ($user) {
            $this->addRewardPointsToUser($user, $rewardPoints);
        }
        return $order;
    }
}
