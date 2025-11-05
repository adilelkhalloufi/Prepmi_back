<?php

namespace App\Http\Controllers;

use App\Http\Resources\MealPreparationResource;
use App\Models\Order;
use App\Models\OrderMeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Enum\OrderStatus;
use App\Models\StatusHistory;
use App\Models\LoyaltyTransaction;
use App\Services\UserNutritionService;
use Illuminate\Support\Facades\Auth;

class MealPreparationController extends Controller
{
    /**
     * Display a listing of meal preparations.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'orderMeals.meal'])
            ->whereNotIn('statue', ['cancelled', 'delivered'])
            ->get();
        return response()->json([
            'data' => $orders
        ]);
    }

    /**
     * Display the specified meal preparation.
     */
    public function show($id): JsonResponse
    {
        $preparation = OrderMeal::with(['order.user', 'meal'])
            ->findOrFail($id);

        return response()->json([
            'data' => new MealPreparationResource($preparation)
        ]);
    }

    /**
     * Update the preparation status.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'statue' => 'required|in:' . implode(',', OrderStatus::values()),
        ]);

        $order = Order::find($id);

        // Record status change in history
        StatusHistory::create([
            'order_id' => $id,
            'old_status' => $order->statue,
            'new_status' => $request->statue,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);

        // If order is being cancelled, refund the loyalty points earned from this order
        if ($request->statue == OrderStatus::CANCELLED->value && $order->user_id) {
            $user = $order->user;
            if ($user && $order->reward_point > 0) {
                // Deduct the points that were earned from this order
                $user->total_points_earned -= $order->reward_point;
                // Ensure points don't go below 0
                if ($user->total_points_earned < 0) {
                    $user->total_points_earned = 0;
                }
                $user->save();

                // Record the loyalty transaction for cancellation
                // LoyaltyTransaction::create([
                //     'user_id' => $user->id,
                //     'order_id' => $id,
                //     'type' => 'redeemed',
                //     'points' => -$order->reward_point,
                //     'description' => 'Points refunded due to order cancellation',
                //     'metadata' => ['cancelled_order_id' => $id],
                // ]);
            }
        }

        $order->statue = $request->statue;
        $order->save();
        // If delivered, calculate nutrition and update user
        if ($request->statue == OrderStatus::DELIVERED->value) {
            app(UserNutritionService::class)
                ->updateDailySummary($order->user_id, now()->toDateString());
        }

        $orders = Order::with(['user', 'orderMeals.meal'])
            ->whereNotIn('statue', ['cancelled', 'delivered'])
            ->get();
        return response()->json([
            'data' => $orders
        ]);
    }

   

  
  
}
