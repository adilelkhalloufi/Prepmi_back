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

        //
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
