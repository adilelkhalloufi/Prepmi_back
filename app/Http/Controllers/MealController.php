<?php

namespace App\Http\Controllers;

use App\enum\MealCategory;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MealController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index2 (Request $request)
    {
        $meals = Meal::all();

        // Filtering logic can be added here based on request parameters

 
        return MealResource::collection($meals);
    }
    public function index(Request $request)
    {

        $query = Meal::query();

    

        // Only filter by active status if the param is present
        if ($request->has('active')) {
            $raw = $request->input('active');
            $active = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active === null) {
                $active = is_numeric($raw) ? (int) $raw === 1 : null;
            }
            if ($active !== null) {
                $query->where('is_active', $active ? 1 : 0);
            }
        }

     
        // Only filter by category if the param is present
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Only filter by type if the param is present
        if ($request->has('type_id')) {
            $query->where('type_id', $request->type_id);
        }


        // Filter by dietary preferences
        if ($request->has('vegetarian') && $request->boolean('vegetarian')) {
            $query->where('is_vegetarian', true);
        }
        if ($request->has('vegan') && $request->boolean('vegan')) {
            $query->where('is_vegan', true);
        }
        if ($request->has('gluten_free') && $request->boolean('gluten_free')) {
            $query->where('is_gluten_free', true);
        }
        if ($request->has('keto') && $request->boolean('keto')) {
            $query->where('is_keto', true);
        }
        if ($request->has('paleo') && $request->boolean('paleo')) {
            $query->where('is_paleo', true);
        }
        if ($request->has('low_carb') && $request->boolean('low_carb')) {
            $query->where('is_low_carb', true);
        }
        if ($request->has('high_protein') && $request->boolean('high_protein')) {
            $query->where('is_high_protein', true);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by calories range
        if ($request->has('min_calories')) {
            $query->where('calories', '>=', $request->min_calories);
        }
        if ($request->has('max_calories')) {
            $query->where('calories', '<=', $request->max_calories);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }



        // Sorting add sorte with show_for_membership


        // add sorting with show_for_membership first
        $query->orderBy('is_membership', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 100);
        $meals = $query->paginate($perPage);

        return MealResource::collection($meals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMealRequest $request)
    {
        $validated = $request->validated();

        // Handle main image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('meals/images', $imageName, 'public');
            $validated['image_path'] = $imagePath;
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $galleryImage) {
                $galleryName = time() . '_' . uniqid() . '.' . $galleryImage->getClientOriginalExtension();
                $galleryPath = $galleryImage->storeAs('meals/gallery', $galleryName, 'public');
                $galleryPaths[] = $galleryPath;
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Meal::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $meal = Meal::create($validated);

        return response()->json([
            'message' => 'Meal created successfully',
            'data' => new MealResource($meal)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $meal = Meal::findOrFail($id);

        return response()->json([
            'data' => new MealResource($meal)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMealRequest $request, string $id)
    {
        $meal = Meal::findOrFail($id);
        $validated = $request->validated();

        // Handle main image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($meal->image_path && Storage::disk('public')->exists($meal->image_path)) {
                Storage::disk('public')->delete($meal->image_path);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('meals/images', $imageName, 'public');
            $validated['image_path'] = $imagePath;
        }

        // Handle gallery images upload
        if ($request->hasFile('gallery_images')) {
            // Delete old gallery images if exist
            if ($meal->gallery_images && is_array($meal->gallery_images)) {
                foreach ($meal->gallery_images as $oldImage) {
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }

            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $galleryImage) {
                $galleryName = time() . '_' . uniqid() . '.' . $galleryImage->getClientOriginalExtension();
                $galleryPath = $galleryImage->storeAs('meals/gallery', $galleryName, 'public');
                $galleryPaths[] = $galleryPath;
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        // Update slug if name is changed
        if (isset($validated['name']) && $validated['name'] !== $meal->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Meal::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $meal->update($validated);

        return response()->json([
            'message' => 'Meal updated successfully',
            'data' => new MealResource($meal)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meal = Meal::findOrFail($id);

        // Soft delete
        $meal->delete();

        return response()->json([
            'message' => 'Meal deleted successfully'
        ]);
    }

    /**
     * Get meal by slug
     */
    public function getBySlug(string $slug)
    {
        $meal = Meal::where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => new MealResource($meal)
        ]);
    }

    /**
     * Get featured meals (active meals with high ratings or specific criteria)
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', 6);

        $meals = Meal::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return MealResource::collection($meals);
    }

    /**
     * Get meals by dietary preference
     */
    public function getByDiet(Request $request)
    {
        $diet = $request->get('diet');
        $perPage = $request->get('per_page', 15);

        $query = Meal::where('is_active', true);

        switch ($diet) {
            case 'vegetarian':
                $query->where('is_vegetarian', true);
                break;
            case 'vegan':
                $query->where('is_vegan', true);
                break;
            case 'gluten_free':
                $query->where('is_gluten_free', true);
                break;
            case 'keto':
                $query->where('is_keto', true);
                break;
            case 'paleo':
                $query->where('is_paleo', true);
                break;
            case 'low_carb':
                $query->where('is_low_carb', true);
                break;
            case 'high_protein':
                $query->where('is_high_protein', true);
                break;
            default:
                return response()->json(['message' => 'Invalid diet type'], 400);
        }

        $meals = $query->paginate($perPage);

        return MealResource::collection($meals);
    }

    /**
     * Restore a soft-deleted meal
     */
    public function restore(string $id)
    {
        $meal = Meal::withTrashed()->findOrFail($id);

        if (!$meal->trashed()) {
            return response()->json([
                'message' => 'Meal is not deleted'
            ], 400);
        }

        $meal->restore();

        return response()->json([
            'message' => 'Meal restored successfully',
            'data' => new MealResource($meal)
        ]);
    }

    /**
     * Permanently delete a meal
     */
    public function forceDelete(string $id)
    {
        $meal = Meal::withTrashed()->findOrFail($id);

        // Delete image files if they exist
        if ($meal->image_path && Storage::disk('public')->exists($meal->image_path)) {
            Storage::disk('public')->delete($meal->image_path);
        }

        if ($meal->gallery_images && is_array($meal->gallery_images)) {
            foreach ($meal->gallery_images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $meal->forceDelete();

        return response()->json([
            'message' => 'Meal permanently deleted'
        ]);
    }

    /**
     * Get all meal categories
     */
    public function getCategories()
    {
        return response()->json([
            'data' => MealCategory::toArray()
        ]);
    }

    /**
     * Upload main image
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('meals/images', $imageName, 'public');

            return response()->json([
                'message' => 'Image uploaded successfully',
                'path' => $imagePath,
                'url' => Storage::url($imagePath)
            ], 201);
        }

        return response()->json(['message' => 'No image provided'], 400);
    }

    /**
     * Upload gallery images
     */
    public function uploadGalleryImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('meals/gallery', $imageName, 'public');

                $uploadedImages[] = [
                    'path' => $imagePath,
                    'url' => Storage::url($imagePath)
                ];
            }
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages
        ], 201);
    }

    /**
     * Delete an uploaded image
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            return response()->json([
                'message' => 'Image deleted successfully'
            ]);
        }

        return response()->json([
            'message' => 'Image not found'
        ], 404);
    }
}
