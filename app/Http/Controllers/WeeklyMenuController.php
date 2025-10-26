<?php

namespace App\Http\Controllers;

use App\Models\WeeklyMenu;
use App\Models\Meal;
use App\Http\Requests\StoreWeeklyMenuRequest;
use App\Http\Requests\UpdateWeeklyMenuRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WeeklyMenuController extends Controller
{
    /**
     * Display a listing of weekly plans
     */
    public function index(Request $request)
    {
        $query = WeeklyMenu::with('meals');

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('week_start_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('week_start_date', '<=', $request->to_date);
        }

        // Filter by meal type if provided
        if ($request->has('type_id')) {
            $mealType = $request->type_id;
            $query->whereHas('meals', function ($q) use ($mealType) {
                $q->where('type_id', $mealType);
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $weeklyPlans = $query->orderBy('week_start_date', 'desc')->paginate($perPage);

        return response()->json($weeklyPlans);
    }

    /**
     * Get the current week's plan
     */
    public function getCurrentWeek()
    {
        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek();

        $currentWeek = WeeklyMenu::with('meals')
            ->where('week_start_date', '<=', $startOfWeek)
            ->orderBy('week_start_date', 'desc')
            ->first();

        if (!$currentWeek) {
            return response()->json([
                'success' => false,
                'message' => 'No weekly plan available for this week'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currentWeek
        ]);
    }

    /**
     * Get upcoming weekly plans
     */
    public function getUpcoming(Request $request)
    {
        $limit = $request->get('limit', 4);
        $now = Carbon::now()->startOfWeek();

        $upcomingPlans = WeeklyMenu::with('meals')
            ->where('week_start_date', '>', $now)
            ->orderBy('week_start_date', 'asc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $upcomingPlans
        ]);
    }

    /**
     * Display a specific weekly plan
     */
    public function show($id)
    {
        $weeklyPlan = WeeklyMenu::with('meals')->find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $weeklyPlan
        ]);
    }

    /**
     * Get all meals for a specific weekly plan
     */
    public function getMeals($id)
    {
        $weeklyPlan = WeeklyMenu::with(['meals' => function ($query) {
            $query->select('meals.*');
        }])->find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $weeklyPlan->meals
        ]);
    }

    /**
     * Store a new weekly plan
     */
    public function store(StoreWeeklyMenuRequest $request)
    {
        $weeklyPlan = WeeklyMenu::create([
            'week_start_date' => $request->week_start_date
        ]);

        // Attach meals if provided
        if ($request->has('meal_ids')) {
            $weeklyPlan->meals()->attach($request->meal_ids);
        }

        $weeklyPlan->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan created successfully',
            'data' => $weeklyPlan
        ], 201);
    }

    /**
     * Update a weekly plan
     */
    public function update(UpdateWeeklyMenuRequest $request, $id)
    {
        $weeklyPlan = WeeklyMenu::find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        if ($request->has('week_start_date')) {
            $weeklyPlan->week_start_date = $request->week_start_date;
        }
        // Update other fields if provided
        if ($request->has('description')) {
            $weeklyPlan->description = $request->description;
        }
        if ($request->has('is_active')) {
            $weeklyPlan->is_active = $request->is_active;
        }
        if ($request->has('is_published')) {
            $weeklyPlan->is_published = $request->is_published;
        }
        if ($request->has('title')) {
            $weeklyPlan->title = $request->title;
        }


        $weeklyPlan->save();

        // Update meals if provided
        if ($request->has('meal_ids')) {
            $weeklyPlan->meals()->sync($request->meal_ids);
        }



        $weeklyPlan->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan updated successfully',
            'data' => $weeklyPlan
        ]);
    }

    /**
     * Delete a weekly plan
     */
    public function destroy($id)
    {
        $weeklyPlan = WeeklyMenu::find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        // Detach all meals before deleting
        $weeklyPlan->meals()->detach();
        $weeklyPlan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan deleted successfully'
        ]);
    }

    /**
     * Attach meals to a weekly plan
     */
    public function attachMeals(Request $request, $id)
    {
        $weeklyPlan = WeeklyMenu::find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'meal_ids' => 'required|array',
            'meal_ids.*' => 'exists:meals,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attach meals (won't duplicate if already attached)
        $weeklyPlan->meals()->syncWithoutDetaching($request->meal_ids);
        $weeklyPlan->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Meals attached successfully',
            'data' => $weeklyPlan
        ]);
    }

    /**
     * Detach a meal from a weekly plan
     */
    public function detachMeal($id, $mealId)
    {
        $weeklyPlan = WeeklyMenu::find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        $meal = Meal::find($mealId);

        if (!$meal) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found'
            ], 404);
        }

        $weeklyPlan->meals()->detach($mealId);
        $weeklyPlan->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Meal detached successfully',
            'data' => $weeklyPlan
        ]);
    }

    /**
     * Publish a weekly plan (optional - for future status management)
     */
    public function publish($id)
    {
        $weeklyPlan = WeeklyMenu::find($id);

        if (!$weeklyPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly plan not found'
            ], 404);
        }

        // You can add a 'status' field to weekly_menus table if needed
        // $weeklyPlan->status = 'published';
        // $weeklyPlan->save();

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan published successfully',
            'data' => $weeklyPlan
        ]);
    }
}
