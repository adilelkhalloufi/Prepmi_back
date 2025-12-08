<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of membership plans.
     */
    public function index(Request $request)
    {
        $query = MembershipPlan::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Order by
        $orderBy = $request->get('order_by', 'monthly_fee');
        $orderDirection = $request->get('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 15);
        
        if ($request->boolean('paginate', true)) {
            $plans = $query->paginate($perPage);
        } else {
            $plans = $query->get();
        }

        return response()->json($plans);
    }

    /**
     * Store a newly created membership plan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:membership_plans,name',
            'description' => 'nullable|string',
            'monthly_fee' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'delivery_slots' => 'nullable|integer|min:1',
            'includes_free_desserts' => 'nullable|boolean',
            'free_desserts_quantity' => 'nullable|integer|min:0',
            'perks' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'billing_day_of_month' => 'nullable|integer|min:1|max:28',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = MembershipPlan::create($validator->validated());

        return response()->json([
            'message' => 'Membership plan created successfully',
            'data' => $plan
        ], 201);
    }

    /**
     * Display the specified membership plan.
     */
    public function show($id)
    {
        $plan = MembershipPlan::with(['memberships', 'activeMemberships'])->find($id);

        if (!$plan) {
            return response()->json([
                'message' => 'Membership plan not found'
            ], 404);
        }

        return response()->json($plan);
    }

    /**
     * Update the specified membership plan.
     */
    public function update(Request $request, $id)
    {
        $plan = MembershipPlan::find($id);

        if (!$plan) {
            return response()->json([
                'message' => 'Membership plan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:membership_plans,name,' . $id,
            'description' => 'nullable|string',
            'monthly_fee' => 'sometimes|required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'delivery_slots' => 'nullable|integer|min:1',
            'includes_free_desserts' => 'nullable|boolean',
            'free_desserts_quantity' => 'nullable|integer|min:0',
            'perks' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'billing_day_of_month' => 'nullable|integer|min:1|max:28',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan->update($validator->validated());

        return response()->json([
            'message' => 'Membership plan updated successfully',
            'data' => $plan
        ]);
    }

    /**
     * Remove the specified membership plan.
     */
    public function destroy($id)
    {
        $plan = MembershipPlan::find($id);

        if (!$plan) {
            return response()->json([
                'message' => 'Membership plan not found'
            ], 404);
        }

        // Check if plan has active memberships
        $activeMembershipsCount = $plan->activeMemberships()->count();
        
        if ($activeMembershipsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete plan with active memberships',
                'active_memberships_count' => $activeMembershipsCount
            ], 400);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Membership plan deleted successfully'
        ]);
    }

    /**
     * Toggle active status of membership plan.
     */
    public function toggleActive($id)
    {
        $plan = MembershipPlan::find($id);

        if (!$plan) {
            return response()->json([
                'message' => 'Membership plan not found'
            ], 404);
        }

        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json([
            'message' => 'Plan status updated successfully',
            'data' => $plan
        ]);
    }

    /**
     * Get popular plans (most subscribers).
     */
    public function popular(Request $request)
    {
        $limit = $request->get('limit', 5);

        $plans = MembershipPlan::where('is_active', true)
            ->withCount(['memberships' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('memberships_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($plans);
    }
}
