<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionWeeklySelection;
use App\Models\WeeklyMenu;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionWeeklySelectionController extends Controller
{
    /**
     * Get all weekly selections for a subscription
     */
    public function index($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        // Verify the subscription belongs to the authenticated user
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $selections = $subscription->weeklySelections()
            ->with(['meals', 'weeklyMenu'])
            ->orderBy('week_start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $selections
        ]);
    }

    /**
     * Get a specific weekly selection
     */
    public function show($subscriptionId, $selectionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $selection = SubscriptionWeeklySelection::with(['meals', 'weeklyMenu'])
            ->where('subscription_id', $subscriptionId)
            ->findOrFail($selectionId);

        return response()->json([
            'success' => true,
            'data' => $selection
        ]);
    }

    /**
     * Create or update weekly meal selection
     */
    public function updateSelection(Request $request, $subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'week_start_date' => 'required|date',
            'meal_ids' => 'required|array',
            'meal_ids.*' => 'exists:meals,id',
            'delivery_notes' => 'nullable|string',
            'special_instructions' => 'nullable|string',
        ]);

        $weekStartDate = Carbon::parse($request->week_start_date)->startOfWeek();
        $weekEndDate = $weekStartDate->copy()->endOfWeek();

        // Get the weekly menu for this week
        $weeklyMenu = WeeklyMenu::where('week_start_date', $weekStartDate)
            ->first();

        if (!$weeklyMenu) {
            return response()->json([
                'success' => false,
                'message' => 'No menu available for the selected week'
            ], 404);
        }

        // Validate meal count matches plan
        $plan = $subscription->plan;
        $totalMeals = count($request->meal_ids);
        
        if ($totalMeals !== $plan->meals_per_week) {
            return response()->json([
                'success' => false,
                'message' => "You must select exactly {$plan->meals_per_week} meals for your plan"
            ], 422);
        }

        // Validate all selected meals are in the weekly menu
        $availableMealIds = $weeklyMenu->meals()->pluck('meals.id')->toArray();
        $invalidMeals = array_diff($request->meal_ids, $availableMealIds);
        
        if (!empty($invalidMeals)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected meals are not available in this week\'s menu'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find or create the weekly selection
            $selection = SubscriptionWeeklySelection::firstOrCreate(
                [
                    'subscription_id' => $subscriptionId,
                    'week_start_date' => $weekStartDate,
                ],
                [
                    'weekly_menu_id' => $weeklyMenu->id,
                    'week_end_date' => $weekEndDate,
                    'week_number' => $this->calculateWeekNumber($subscription, $weekStartDate),
                    'scheduled_delivery_date' => $subscription->next_delivery_date,
                    'delivery_notes' => $request->delivery_notes,
                    'special_instructions' => $request->special_instructions,
                ]
            );

            // Check if selection can be modified
            if (!$selection->canModify()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This selection is locked and cannot be modified'
                ], 403);
            }

            // Update delivery notes if provided
            if ($request->has('delivery_notes') || $request->has('special_instructions')) {
                $selection->update([
                    'delivery_notes' => $request->delivery_notes,
                    'special_instructions' => $request->special_instructions,
                ]);
            }

            // Sync meals with additional pivot data
            $mealData = [];
            foreach ($request->meal_ids as $index => $mealId) {
                $meal = Meal::find($mealId);
                $mealData[$mealId] = [
                    'quantity' => 1,
                    'position' => $index + 1,
                    'price_at_selection' => $meal->price,
                    'selected_at' => now(),
                    'modified_at' => now(),
                ];
            }
            
            $selection->meals()->sync($mealData);

            DB::commit();

            $selection->load(['meals', 'weeklyMenu']);

            return response()->json([
                'success' => true,
                'message' => 'Weekly meal selection updated successfully',
                'data' => $selection
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update selection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm a weekly selection
     */
    public function confirmSelection($subscriptionId, $selectionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $selection = SubscriptionWeeklySelection::where('subscription_id', $subscriptionId)
            ->findOrFail($selectionId);

        if (!$selection->canModify()) {
            return response()->json([
                'success' => false,
                'message' => 'This selection is already locked or delivered'
            ], 403);
        }

        if (!$selection->isSelectionComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your meal selection before confirming'
            ], 422);
        }

        $selection->confirmSelection();

        return response()->json([
            'success' => true,
            'message' => 'Selection confirmed successfully',
            'data' => $selection->fresh(['meals', 'weeklyMenu'])
        ]);
    }

    /**
     * Get available meals for a specific week
     */
    public function getAvailableMeals($subscriptionId, Request $request)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'week_start_date' => 'required|date'
        ]);

        $weekStartDate = Carbon::parse($request->week_start_date)->startOfWeek();

        $weeklyMenu = WeeklyMenu::where('week_start_date', $weekStartDate)
            ->with('meals')
            ->first();

        if (!$weeklyMenu) {
            return response()->json([
                'success' => false,
                'message' => 'No menu available for the selected week'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'weekly_menu' => $weeklyMenu,
                'meals' => $weeklyMenu->meals,
                'required_meals' => $subscription->plan->meals_per_week
            ]
        ]);
    }

    /**
     * Delete/reset a weekly selection
     */
    public function destroy($subscriptionId, $selectionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $selection = SubscriptionWeeklySelection::where('subscription_id', $subscriptionId)
            ->findOrFail($selectionId);

        if (!$selection->canModify()) {
            return response()->json([
                'success' => false,
                'message' => 'This selection is locked and cannot be deleted'
            ], 403);
        }

        $selection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weekly selection deleted successfully'
        ]);
    }

    /**
     * Get upcoming weeks that need meal selection
     */
    public function upcomingWeeks($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get next 4 weeks of menus
        $upcomingMenus = WeeklyMenu::where('week_start_date', '>=', now()->startOfWeek())
            ->orderBy('week_start_date')
            ->limit(4)
            ->get();

        $weeksData = [];
        foreach ($upcomingMenus as $menu) {
            $selection = SubscriptionWeeklySelection::where('subscription_id', $subscriptionId)
                ->where('week_start_date', $menu->week_start_date)
                ->with('meals')
                ->first();

            $weeksData[] = [
                'weekly_menu' => $menu,
                'selection' => $selection,
                'has_selection' => $selection !== null,
                'is_complete' => $selection ? $selection->isSelectionComplete() : false,
                'can_modify' => $selection ? $selection->canModify() : true,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $weeksData
        ]);
    }

    /**
     * Calculate week number within subscription
     */
    private function calculateWeekNumber(Subscription $subscription, Carbon $weekStartDate): int
    {
        $subscriptionStart = Carbon::parse($subscription->started_at)->startOfWeek();
        $weeksDiff = $subscriptionStart->diffInWeeks($weekStartDate);
        
        // Week number cycles 1-4 for monthly subscriptions
        return ($weeksDiff % 4) + 1;
    }
}
