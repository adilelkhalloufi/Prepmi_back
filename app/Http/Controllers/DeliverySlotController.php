<?php

namespace App\Http\Controllers;

use App\enum\MembershipStatus;
use App\Http\Resources\DeliverySlotsResource;
use App\Models\DeliverySlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliverySlotController extends Controller
{
    /**
     * Get all available delivery slots.
     */
    public function index(Request $request): JsonResponse
    {


        $slots =  DeliverySlot::all();

        return response()->json([
            'success' => true,
            'data' => DeliverySlotsResource::collection($slots),
        ]);
    }

    /**
     * Get available delivery slots for authenticated user based on membership status.
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $user = auth()->user();
        $hasMembership = false;

        if ($user) {
            $hasMembership = $user->memberships()
                ->where('status', MembershipStatus::ACTIVE)
                ->exists();
        }

        $query = DeliverySlot::active()->available();

        // Filter by user type
        if ($hasMembership) {
            $query->forMembership();
        } else {
            $query->forNormalUsers();
        }

        // Filter by day of week if provided
        if ($request->has('day_of_week')) {
            $query->where(function ($q) use ($request) {
                $q->where('day_of_week', $request->day_of_week)
                    ->orWhereNull('day_of_week');
            });
        }

        $slots = $query->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'data' => DeliverySlotsResource::collection($slots),
            'meta' => [
                'has_membership' => $hasMembership,
                'user_type' => $hasMembership ? 'membership' : 'normal',
            ]
        ]);
    }

    /**
     * Get a specific delivery slot by ID.
     */
    public function show(DeliverySlot $deliverySlot): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new DeliverySlotsResource($deliverySlot),
        ]);
    }

    /**
     * Get slots filtered by type (membership, normal, both).
     */
    public function getByType(Request $request, string $type): JsonResponse
    {
        $query = DeliverySlot::active()->available();

        switch ($type) {
            case 'membership':
                $query->forMembership();
                break;
            case 'normal':
                $query->forNormalUsers();
                break;
            case 'both':
                // No filter needed, all slots
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot type. Use: membership, normal, or both',
                ], 400);
        }

        if ($request->has('day_of_week')) {
            $query->where(function ($q) use ($request) {
                $q->where('day_of_week', $request->day_of_week)
                    ->orWhereNull('day_of_week');
            });
        }

        $slots = $query->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'data' => DeliverySlotsResource::collection($slots),
            'meta' => [
                'slot_type' => $type,
            ]
        ]);
    }

    /**
     * Store a newly created delivery slot.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slot_name' => 'required|string|max:255',
            'slot_type' => 'required|in:membership,normal,both',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'max_capacity' => 'nullable|integer',
            'day_of_week' => 'required|integer|min:0|max:6',
            'is_active' => 'boolean',
            'price_adjustment' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $slot = DeliverySlot::create(array_merge($validated, [
            'current_bookings' => 0,
            'is_active' => $validated['is_active'] ?? true,
            'price_adjustment' => $validated['price_adjustment'] ?? 0.00,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Delivery slot created successfully',
            'data' => new DeliverySlotsResource($slot),
        ], 201);
    }

    /**
     * Update the specified delivery slot.
     */
    public function update(Request $request, DeliverySlot $deliverySlot): JsonResponse
    {
        $validated = $request->validate([
            'slot_name' => 'sometimes|string|max:255',
            'slot_type' => 'sometimes|in:membership,normal,both',
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
            'max_capacity' => 'nullable|integer',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'is_active' => 'boolean',
            'price_adjustment' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $deliverySlot->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Delivery slot updated successfully',
            'data' => new DeliverySlotsResource($deliverySlot),
        ]);
    }

    /**
     * Remove the specified delivery slot.
     */
    public function destroy(DeliverySlot $deliverySlot): JsonResponse
    {
        // Check if slot has active bookings
        if ($deliverySlot->current_bookings > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete slot with active bookings. Please wait until all deliveries are completed.',
            ], 422);
        }

        $deliverySlot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery slot deleted successfully',
        ]);
    }

    /**
     * Toggle slot active status.
     */
    public function toggleActive(DeliverySlot $deliverySlot): JsonResponse
    {
        $deliverySlot->update([
            'is_active' => !$deliverySlot->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $deliverySlot->is_active
                ? 'Delivery slot activated successfully'
                : 'Delivery slot deactivated successfully',
            'data' => new DeliverySlotsResource($deliverySlot),
        ]);
    }
}
