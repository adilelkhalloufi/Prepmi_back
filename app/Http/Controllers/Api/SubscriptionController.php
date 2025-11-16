<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with(['user', 'plan', 'orders'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when(auth()->user()->role !== 'admin', fn($q) => $q->where('user_id', auth()->id()))
            ->latest('created_at')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => SubscriptionResource::collection($subscriptions),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ]
        ]);
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load(['user', 'plan', 'orders']);

        return response()->json([
            'success' => true,
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|string',
            'next_delivery_date' => 'sometimes|date',
            'delivery_address' => 'sometimes|string',
            'delivery_notes' => 'sometimes|string|nullable',
            'special_instructions' => 'sometimes|string|nullable',
            'preferred_delivery_days' => 'sometimes|array',
            'auto_renew' => 'sometimes|boolean',
        ]);

        $updateData = $request->only([
            'status',
            'next_delivery_date',
            'delivery_address',
            'delivery_notes',
            'special_instructions',
            'auto_renew'
        ]);

        if ($request->has('preferred_delivery_days')) {
            $updateData['preferred_delivery_days'] = json_encode($request->preferred_delivery_days);
        }

        $subscription->update($updateData);
        $subscription->load(['user', 'plan', 'order']);

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Pause the subscription.
     */
    public function pause(Request $request, Subscription $subscription): JsonResponse
    {



        $subscription->update([
            'paused_at' => now(),
            'pause_reason' => 'Client requested pause',
            'pause_start_date' => now(),
            'pause_end_date' => now()->addWeeks($request->pause_weeks),
            'paused_weeks_used' => $subscription->paused_weeks_used + 1,
            'status' => 'paused',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription paused successfully',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Resume the subscription.
     */
    public function resume(Subscription $subscription): JsonResponse
    {



        $subscription->update([
            'paused_at' => null,
            'pause_reason' => null,
            'pause_start_date' => null,
            'pause_end_date' => null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription resumed successfully 1',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {

        $subscription->update([
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled by user request',
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Reactivate a cancelled subscription.
     */
    public function reactivate(Subscription $subscription): JsonResponse
    {


        $subscription->update([
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription reactivated successfully',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Toggle auto-renewal for the subscription.
     */
    public function toggleAutoRenew(Subscription $subscription): JsonResponse
    {
        $newAutoRenew = !$subscription->auto_renew;

        $subscription->update([
            'auto_renew' => $newAutoRenew,
            'auto_renew_disabled_at' => $newAutoRenew ? null : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $newAutoRenew ? 'Auto-renewal enabled' : 'Auto-renewal disabled',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Get subscription statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'paused_subscriptions' => Subscription::where('status', 'paused')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'total_revenue' => Subscription::sum('total_amount_paid'),
            'average_weeks_committed' => Subscription::avg('weeks_committed'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
