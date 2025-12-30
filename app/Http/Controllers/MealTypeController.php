<?php

namespace App\Http\Controllers;

use App\Models\MealType;
use App\Http\Resources\MealTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MealTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $mealTypes = MealType::active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => MealTypeResource::collection($mealTypes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:meal_types,name',
            'slug' => 'required|string|max:255|unique:meal_types,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $mealType = MealType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meal type created successfully',
            'data' => new MealTypeResource($mealType),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MealType $mealType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new MealTypeResource($mealType),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MealType $mealType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:meal_types,name,' . $mealType->id,
            'slug' => 'sometimes|string|max:255|unique:meal_types,slug,' . $mealType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $mealType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meal type updated successfully',
            'data' => new MealTypeResource($mealType),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MealType $mealType): JsonResponse
    {
        $mealType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meal type deleted successfully',
        ]);
    }
}
