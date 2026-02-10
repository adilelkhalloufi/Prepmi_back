<?php

namespace App\Services;

use App\Enum\OrderStatus;
use App\Enum\UserRole;
use App\Models\Order;
use App\Models\Plan;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderService
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Create a subscription for an order.
     */
    public function createSubscriptionForOrder(Order $order, Plan $plan, $userId)
    {
        $subscription = \App\Models\Subscription::create([
            'user_id' => $userId,
            'plan_id' => $plan->id,
            'order_id' => $order->id,
            'status' => \App\Enum\SubscriptionStatus::ACTIVE->value,
            'started_at' => now(),
            'ends_at' => now()->addWeeks(4), // Default 4 weeks subscription
            'next_billing_date' => now()->addWeek(), // Next billing in 1 week (weekly billing)
            'next_delivery_date' => now()->addDays(3), // First delivery in 3 days
            'cancellation_deadline' => now()->addDays(5), // 2 days before next billing
            'paused_at' => null,
            'pause_reason' => null,
            'pause_start_date' => null,
            'pause_end_date' => null,
            'max_pause_weeks' => 4,
            'paused_weeks_used' => 0,
            'preferred_delivery_days' => json_encode(['monday', 'wednesday', 'friday']),
            'delivery_restrictions' => null,
            'auto_renew' => true,
            'auto_renew_disabled_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'weeks_committed' => 4, // Default 4 weeks commitment
            'weeks_remaining' => 4,
            'total_amount_paid' => $plan->price_subscription_per_week ?? $plan->price_per_week,
            'meals_delivered' => 0,
            'delivery_address' => $order->adresse_livrsion,
            'delivery_notes' => null,
            'special_instructions' => null,
        ]);

        // Update order to link to subscription
        $order->update(['subscription_id' => $subscription->id]);

        Log::info('Subscription created for order', [
            'subscription_id' => $subscription->id,
            'order_id' => $order->id,
            'user_id' => $userId
        ]);

        return $subscription;
    }

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
     * Attach free drinks to order.
     */
    public function attachFreeDrinksToOrder(Order $order, array $freeDrinks, $membershipId)
    {
        foreach ($freeDrinks as $drink) {
            $drinkId = $drink['id'] ?? null;
            $quantity = $drink['quantity'] ?? 1;

            if ($drinkId) {
                // Attach drink to order as a meal with price 0
                $order->meals()->attach($drinkId, [
                    'quantity' => $quantity,
                    'membership_id' => $membershipId,
                    'price' => 0, // Free drink
                    'is_reward_meal' => false,
                ]);
            }
        }
    }

    /**
     * Create delivery record for order with selected slot.
     */
    public function createDeliveryForOrder(Order $order, $deliverySlotId)
    {
        $slot = \App\Models\DeliverySlot::find($deliverySlotId);

        if (!$slot) {
            Log::warning('Delivery slot not found', ['slot_id' => $deliverySlotId]);
            return null;
        }

        // // Check if slot is available
        // if (!$slot->isAvailable()) {
        //     Log::warning('Delivery slot not available', ['slot_id' => $deliverySlotId]);
        //     return null;
        // }


        // // Book the slot
        // $slot->book();

        // Create delivery record
        $delivery = \App\Models\Delivery::create([
            'order_id' => $order->id,
            'delivery_slot_id' => $deliverySlotId,
            'delivery_window_start' => now()->setTimeFromTimeString($slot->start_time),
            'delivery_window_end' => now()->setTimeFromTimeString($slot->end_time),
            'status' => \App\Enum\DeliveryStatus::PENDING,
            'notes' => 'Delivery scheduled in ' . $slot->slot_name,
        ]);


        return $delivery;
    }

    /**
     * Add reward points to a user. If total_points_earned >= target, reset and create a reward.
     */
    public function addRewardPointsToUser($user, $points = 1)
    {
        $user->total_points_earned += $points;
        $targetPoints = (int) $this->settingService->getValue('system_points_per_order', 12);
        if ($user->total_points_earned >= $targetPoints) {
            $user->total_points_earned = $user->total_points_earned - $targetPoints;
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
        $freeDrinks = $data['freeDrinks'] ?? [];
        $purchaseType = $data['purchaseType'] ?? null;
        $paymentMethod = $data['paymentMethod'] ?? null;
        $userId = Auth::id() ?? $data['user_id'] ?? null;
        $totalAmount = $data['totalAmount'] ?? 0;
        $membershipId = $data['membershipId'] ?? null;
        $deliverySlotIds = $data['delivery_slot_ids'] ?? [];

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
            'size' => $infos['size'] ?? "small",
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

        // Handle free drinks if provided
        if (!empty($freeDrinks)) {
            $this->attachFreeDrinksToOrder($order, $freeDrinks, $membershipId);
        }

        // Create subscription if purchaseType is subscription
        if ($purchaseType === 'subscription' && $plan && $userId) {
            $this->createSubscriptionForOrder($order, $plan, $userId);
        }

        // Create delivery records for selected slots (up to 3)
        if (!empty($deliverySlotIds)) {
            foreach ($deliverySlotIds as $deliverySlotId) {
                $this->createDeliveryForOrder($order, $deliverySlotId);
            }
        }

        // Add reward points to user
        $user = \App\Models\User::find($userId);
        if ($user) {
            $this->addRewardPointsToUser($user, $rewardPoints);
        }

        // Send order confirmation email
        try {
            $email = $order->user->email ?? $infos['email'] ?? null;
            if ($email) {
                $order->load(['meals', 'deliveries.deliverySlot']);
                Mail::to($email)->send(new OrderConfirmation($order));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        return $order;
    }
}
