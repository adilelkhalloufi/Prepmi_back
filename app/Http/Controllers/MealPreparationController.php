<?php

namespace App\Http\Controllers;

use App\Http\Resources\MealPreparationResource;
use App\Models\Order;
use App\Models\OrderMeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MealPreparationController extends Controller
{
    /**
     * Display a listing of meal preparations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrderMeal::with(['order.user', 'meal']);

        // Filter by preparation status (order status)
        if ($request->filled('preparation_status')) {
            $query->whereHas('order', function ($q) use ($request) {
                $statusMap = [
                    'pending' => 'pending',
                    'preparing' => 'preparing',
                    'ready' => 'ready_for_delivery',
                    'delivered' => 'delivered',
                    'cancelled' => 'cancelled',
                ];
                $status = $statusMap[$request->preparation_status] ?? $request->preparation_status;
                $q->where('status', $status);
            });
        }

        // Filter by preparation date (order date)
        if ($request->filled('preparation_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('order_date', $request->preparation_date);
            });
        }

        // Filter by delivery date
        if ($request->filled('delivery_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('delivery_date', $request->delivery_date);
            });
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereBetween('delivery_date', [$request->start_date, $request->end_date]);
            });
        }

        // Filter by meal
        if ($request->filled('meal_id')) {
            $query->where('meal_id', $request->meal_id);
        }

        // Filter by order
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        // Search by customer name
        if ($request->filled('customer_name')) {
            $query->whereHas('order.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Sort by preparation date (order date) or delivery date
        $sortBy = $request->input('sort_by', 'delivery_date');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if ($sortBy === 'delivery_date') {
            $query->join('orders', 'order_meals.order_id', '=', 'orders.id')
                ->orderBy('orders.delivery_date', $sortOrder)
                ->select('order_meals.*');
        } elseif ($sortBy === 'preparation_date') {
            $query->join('orders', 'order_meals.order_id', '=', 'orders.id')
                ->orderBy('orders.order_date', $sortOrder)
                ->select('order_meals.*');
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        
        if ($request->boolean('all')) {
            $preparations = $query->get();
            return MealPreparationResource::collection($preparations);
        }

        $preparations = $query->paginate($perPage);
        
        return MealPreparationResource::collection($preparations);
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
            'preparation_status' => 'required|in:pending,preparing,ready,delivered,cancelled',
        ]);

        $orderMeal = OrderMeal::with(['order'])->findOrFail($id);
        
        // Map preparation status to order status
        $statusMap = [
            'pending' => 'pending',
            'preparing' => 'preparing',
            'ready' => 'ready_for_delivery',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $orderStatus = $statusMap[$request->preparation_status];
        $orderMeal->order->update(['status' => $orderStatus]);

        $orderMeal->load(['order.user', 'meal']);

        return response()->json([
            'message' => 'Preparation status updated successfully',
            'data' => new MealPreparationResource($orderMeal)
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
