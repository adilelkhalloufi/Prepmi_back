<?php

namespace App\Http\Controllers;

use App\enum\UserRole;
use App\enum\OrderStatus;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNutritionSummary;
use App\Models\Order;
use App\Models\OrderMeal;
use App\Models\Meal;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for client, admin, and cuisine users.
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        switch ($user->role) {
            case UserRole::ADMIN->value:
                return $this->adminStats();
            case UserRole::CUISINIER->value:
                return $this->cuisineStats();
            case UserRole::CLIENT->value:
                return $this->clientStats();
            default:
                return response()->json(['error' => 'Role not supported'], 403);
        }
    }

    protected function adminStats()
    {
        // Calculate daily revenue (today's orders)
        $dailyRevenue = Order::whereDate('date_order', now()->toDateString())
            ->sum('total_amount');

        // Calculate monthly revenue (current month's orders)
        $monthlyRevenue = Order::whereMonth('date_order', now()->month)
            ->whereYear('date_order', now()->year)
            ->sum('total_amount');

        // Count today's orders
        $todayOrders = Order::whereDate('date_order', now()->toDateString())
            ->count();

        // Count active clients (clients with at least one order)
        $activeClients = User::where('role', UserRole::CLIENT->value)
            ->whereHas('orders')
            ->count();

        // Count total users
        $totalUsers = User::count();

        // Count total meals
        $totalMeals = Meal::count();

        // Count pending orders
        $pendingOrders = Order::where('statue', OrderStatus::PENDING->value)
            ->count();

        // Count total orders
        $totalOrders = Order::count();

        return response()->json([
            'dailyRevenue' => $dailyRevenue ?? 0,
            'monthlyRevenue' => $monthlyRevenue ?? 0,
            'todayOrders' => $todayOrders,
            'activeClients' => $activeClients,
            'totalUsers' => $totalUsers,
            'totalMeals' => $totalMeals,
            'pendingOrders' => $pendingOrders,
            'totalOrders' => $totalOrders,
        ]);
    }

    protected function cuisineStats()
    {
        $cuisineCount = User::where('role', 'cuisine')->count();
        // Add more cuisine-specific stats here
        return response()->json([
            'cuisines' => $cuisineCount,
        ]);
    }

    protected function clientStats()
    {
        $user = Auth::user();

        // UserNutritionSummary: Get the latest summary
        $nutritionSummary = UserNutritionSummary::where('user_id', $user->id)->latest()->first();

        // Orders this month
        $currentMonthOrders = Order::where('user_id', $user->id)
            ->whereMonth('date_order', now()->month)
            ->whereYear('date_order', now()->year)
            ->count();

        // Orders previous month
        $previousMonth = now()->subMonth();
        $previousMonthOrders = Order::where('user_id', $user->id)
            ->whereMonth('date_order', $previousMonth->month)
            ->whereYear('date_order', $previousMonth->year)
            ->count();

        $difference = $currentMonthOrders - $previousMonthOrders;

        // History of meals ordered
        $mealsHistory = OrderMeal::with('meal')
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($orderMeal) {
                return [
                    'meal_name' => $orderMeal->meal->name ?? 'Unknown',
                    'quantity' => $orderMeal->quantity,
                    'price' => $orderMeal->price,
                    'order_date' => $orderMeal->order->date_order,
                ];
            });

        return response()->json([
            'nutrition_summary' => $nutritionSummary,
            'orders_this_month' => $currentMonthOrders,
            'orders_difference' => $difference,
            'meals_history' => $mealsHistory,
            'total_points_earned' => $user->total_points_earned ?? 0,
        ]);
    }
}
