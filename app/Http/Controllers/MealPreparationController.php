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
         $orders = Order::with(['user', 'orderMeals.meal'])->get();
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
            'statue' => 'required',
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
                ->updateDailySummary($order->id_user, now()->toDateString());
        }

        $orders = Order::with(['user', 'orderMeals.meal'])->get();
        return response()->json([
            'data' => $orders
        ]);
    }

    /**
     * Update preparation notes.
     */
    public function updateNotes(Request $request, $id): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $orderMeal = OrderMeal::with(['order'])->findOrFail($id);
        
        // Update order notes
        $orderMeal->order->update(['notes' => $request->notes]);

        $orderMeal->load(['order.user', 'meal']);

        return response()->json([
            'message' => 'Notes updated successfully',
            'data' => new MealPreparationResource($orderMeal)
        ]);
    }

    /**
     * Get preparations by status.
     */
    public function byStatus(Request $request, string $status): AnonymousResourceCollection
    {
        $statusMap = [
            'pending' => 'pending',
            'preparing' => 'preparing',
            'ready' => 'ready_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $orderStatus = $statusMap[$status] ?? $status;

        $preparations = OrderMeal::with(['order.user', 'meal'])
            ->whereHas('order', function ($q) use ($orderStatus) {
                $q->where('status', $orderStatus);
            })
            ->get();

        return MealPreparationResource::collection($preparations);
    }

    /**
     * Get today's preparations.
     */
    public function today(): AnonymousResourceCollection
    {
        $today = now()->toDateString();

        $preparations = OrderMeal::with(['order.user', 'meal'])
            ->whereHas('order', function ($q) use ($today) {
                $q->whereDate('delivery_date', $today);
            })
            ->get();

        return MealPreparationResource::collection($preparations);
    }

    /**
     * Get upcoming preparations.
     */
    public function upcoming(Request $request): AnonymousResourceCollection
    {
        $days = $request->input('days', 7);
        $startDate = now()->toDateString();
        $endDate = now()->addDays($days)->toDateString();

        $preparations = OrderMeal::with(['order.user', 'meal'])
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->whereNotIn('status', ['cancelled', 'delivered']);
            })
            ->get();

        return MealPreparationResource::collection($preparations);
    }

    /**
     * Get preparation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $stats = [
            'total' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate]);
            })->count(),
            
            'pending' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->where('status', 'pending');
            })->count(),
            
            'preparing' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->where('status', 'preparing');
            })->count(),
            
            'ready' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->where('status', 'ready_for_delivery');
            })->count(),
            
            'delivered' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->where('status', 'delivered');
            })->count(),
            
            'cancelled' => OrderMeal::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('delivery_date', [$startDate, $endDate])
                    ->where('status', 'cancelled');
            })->count(),
        ];

        return response()->json([
            'data' => $stats,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
