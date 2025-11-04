<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNutritionSummary;
use App\Models\Order;
use App\Models\OrderMeal;

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
        return $this->clientStats();


        
        switch ($user->role) {
            case 'admin':
                return $this->adminStats();
            case 'cuisine':
                return $this->cuisineStats();
            case 'client':
                return $this->clientStats();
            default:
                return response()->json(['error' => 'Role not supported'], 403);
        }
    }

    protected function adminStats()
    {
        $clientCount = User::where('role', 'client')->count();
        $adminCount = User::where('role', 'admin')->count();
        $cuisineCount = User::where('role', 'cuisine')->count();
        return response()->json([
            'clients' => $clientCount,
            'admins' => $adminCount,
            'cuisines' => $cuisineCount,
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
