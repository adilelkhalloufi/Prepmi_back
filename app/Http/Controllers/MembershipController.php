<?php

namespace App\Http\Controllers;

use App\Enum\MembershipStatus;
use App\Enum\UserRole;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipFreezeHistory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * Display a listing of memberships.
     */
    public function index(Request $request)
    {
        // return "fdsf";
        $query = Membership::with(['user', 'membershipPlan']);

        // If not admin, filter to only show authenticated user's memberships
       if (!auth()->user()->hasRole(UserRole::ADMIN->value)) {
            $query->where('user_id', auth()->id());
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (only if admin)
        if ($request->has('user_id') ) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by plan
        if ($request->has('membership_plan_id')) {
            $query->where('membership_plan_id', $request->membership_plan_id);
        }

        // Filter by date range
        if ($request->has('started_from')) {
            $query->where('started_at', '>=', $request->started_from);
        }

        if ($request->has('started_to')) {
            $query->where('started_at', '<=', $request->started_to);
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 15);
        
        if ($request->boolean('paginate', true)) {
            $memberships = $query->paginate($perPage);
        } else {
            $memberships = $query->get();
        }

        // Add free desserts count for current month to each membership
        $memberships->getCollection()->transform(function ($membership) {
            $freeDessertCount = Order::where('user_id', $membership->user_id)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                
                ->with(['orderMeals' => function ($q) use ($membership) {
                    $q->where('membership_id', $membership->id);
                }])
                ->get()
                ->sum(function ($order) {
                    return $order->orderMeals->sum('quantity');
                });
            
            $membership->free_desserts_used_this_month = $freeDessertCount;
            return $membership;
        });

        return response()->json($memberships);
    }

    /**
     * Store a newly created membership.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
             'membership_plan_id' => 'required|exists:membership_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already has an active membership
        $existingMembership = Membership::where('user_id', auth()->id())
            ->where('status', MembershipStatus::ACTIVE->value)
            ->first();

        if ($existingMembership) {
            return response()->json([
                'message' => 'User already has an active membership',
                'existing_membership' => $existingMembership
            ], 400);
        }

        $plan = MembershipPlan::find($request->membership_plan_id);

        if (!$plan->is_active) {
            return response()->json([
                'message' => 'This membership plan is not currently available'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate next billing date
            $billingDay = $plan->billing_day_of_month;
            $nextBillingDate = Carbon::now()->day($billingDay);
            
            if ($nextBillingDate->isPast()) {
                $nextBillingDate->addMonth();
            }

            // Create membership
            $membership = Membership::create([
                'user_id' => auth()->id(),
                'membership_plan_id' => $plan->id,
                'status' => MembershipStatus::PENDING->value,
                'started_at' => now(),
                'next_billing_date' => $nextBillingDate,
                'current_monthly_fee' => $plan->monthly_fee,
                'discount_percentage' => $plan->discount_percentage,
                'delivery_slots_available' => $plan->delivery_slots,
                'has_received_monthly_desserts' => false,
            ]);

            // Update user
            $user = User::find(auth()->id());
            $user->update([
                'is_member' => true,
                'member_status' => MembershipStatus::PENDING->value,
                'current_membership_id' => $membership->id,
                'member_since' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Membership created successfully',
                'data' => $membership->load(['user', 'membershipPlan'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified membership.
     */
    public function show($id)
    {
        $membership = Membership::with([
            'user',
            'membershipPlan',
            'freezeHistory',
            'transactions',
            'perksUsage'
        ])->find($id);

        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }

        // Check user's orders this month for free desserts
        if ($membership->membershipPlan && $membership->user_id) {
            $hasUsedFreeDesserts = \App\Models\Order::where('user_id', $membership->user_id)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereHas('meals', function ($q) {
                    $q->where('type_id', 4) // DESSERTS
                      ->whereNotNull('membership_id'); // Free desserts have membership_id
                })
                ->exists();
            
            if ($hasUsedFreeDesserts) {
                $membership->membershipPlan->free_desserts_quantity = 0;
            }
        }

        return response()->json($membership);
    }

    /**
     * Activate a pending membership (after payment confirmation).
     */
    public function activate($id)
    {
        $membership = Membership::find($id);
        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }
       

        DB::beginTransaction();
        try {
            $membership->update([
                'status' => MembershipStatus::ACTIVE->value,
                'started_at' => now(),
            ]);

            // Update user status
            $membership->user->update([
                'member_status' => MembershipStatus::ACTIVE->value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Membership activated successfully',
                'data' => $membership->fresh(['user', 'membershipPlan'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to activate membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a membership.
     */
    public function cancel(Request $request, $id)
    {
        $membership = Membership::find($id);

        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }

        if ($membership->status === MembershipStatus::CANCELLED->value) {
            return response()->json([
                'message' => 'Membership is already cancelled'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $membership->update([
                'status' => MembershipStatus::CANCELLED->value,
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason,
                'ends_at' => now(),
            ]);

            // Update user
            $membership->user->update([
                'member_status' => MembershipStatus::CANCELLED->value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Membership cancelled successfully',
                'data' => $membership->fresh(['user', 'membershipPlan'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Freeze a membership.
     */
    public function freeze(Request $request, $id)
    {
        $membership = Membership::find($id);

        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }

        if (!$membership->isActive()) {
            return response()->json([
                'message' => 'Only active memberships can be frozen'
            ], 400);
        }

        if (!$membership->canFreeze()) {
            return response()->json([
                'message' => 'You can only freeze your membership once every 6 months'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'freeze_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update membership status
            $membership->update([
                'status' => MembershipStatus::FROZEN->value,
            ]);

            // Create freeze history record
            $freezeHistory = MembershipFreezeHistory::create([
                'membership_id' => $membership->id,
                'frozen_at' => now(),
                'freeze_reason' => $request->freeze_reason,
                'next_allowed_freeze_date' => now()->addMonths(6),
            ]);

            // Update user
            $membership->user->update([
                'member_status' => MembershipStatus::FROZEN->value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Membership frozen successfully',
                'data' => $membership->fresh(['user', 'membershipPlan', 'freezeHistory'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to freeze membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unfreeze a membership.
     */
    public function unfreeze($id)
    {
        $membership = Membership::find($id);

        if (!$membership) {
            return response()->json([
                'message' => 'Membership not found'
            ], 404);
        }

        if (!$membership->isFrozen()) {
            return response()->json([
                'message' => 'Membership is not frozen'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update membership status
            $membership->update([
                'status' => MembershipStatus::ACTIVE->value,
            ]);

            // Update freeze history
            $activeFreeze = $membership->freezeHistory()
                ->whereNull('unfrozen_at')
                ->first();

            if ($activeFreeze) {
                $activeFreeze->update([
                    'unfrozen_at' => now(),
                    'freeze_duration_days' => $activeFreeze->calculateDuration(),
                ]);
            }

            // Update user
            $membership->user->update([
                'member_status' => MembershipStatus::ACTIVE->value,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Membership unfrozen successfully',
                'data' => $membership->fresh(['user', 'membershipPlan', 'freezeHistory'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to unfreeze membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's current membership.
     */
    public function getCurrentMembership($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $membership = Membership::where('user_id', $userId)
            ->whereIn('status', [
                MembershipStatus::ACTIVE->value,
                MembershipStatus::FROZEN->value,
                MembershipStatus::PENDING->value
            ])
            ->with(['membershipPlan', 'freezeHistory', 'perksUsage'])
            ->first();

        if (!$membership) {
            return response()->json([
                'message' => 'No active membership found for this user'
            ], 404);
        }

        // Check user's orders this month for free desserts
        if ($membership->membershipPlan && $membership->user_id) {
            $hasUsedFreeDesserts = \App\Models\Order::where('user_id', $membership->user_id)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereHas('meals', function ($q) {
                    $q->where('type_id', 4) // DESSERTS
                      ->whereNotNull('membership_id'); // Free desserts have membership_id
                })
                ->exists();
            
            if ($hasUsedFreeDesserts) {
                $membership->membershipPlan->free_desserts_quantity = 0;
            }
        }

        return response()->json($membership);
    }

    /**
     * Get membership statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::where('status', MembershipStatus::ACTIVE->value)->count(),
            'frozen_memberships' => Membership::where('status', MembershipStatus::FROZEN->value)->count(),
            'cancelled_memberships' => Membership::where('status', MembershipStatus::CANCELLED->value)->count(),
            'pending_memberships' => Membership::where('status', MembershipStatus::PENDING->value)->count(),
            'memberships_by_plan' => Membership::select('membership_plan_id', DB::raw('count(*) as count'))
                ->where('status', MembershipStatus::ACTIVE->value)
                ->groupBy('membership_plan_id')
                ->with('membershipPlan:id,name')
                ->get(),
        ];

        return response()->json($stats);
    }
}
