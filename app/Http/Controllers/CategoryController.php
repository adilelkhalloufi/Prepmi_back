<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Category::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Include meal count
        if ($request->boolean('with_meals_count')) {
            $query->withCount('meals');
        }

        // Include meals
        if ($request->boolean('with_meals')) {
            $query->with('meals');
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        
        if ($request->boolean('all')) {
            $categories = $query->get();
            return CategoryResource::collection($categories);
        }

        $categories = $query->paginate($perPage);
        
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category)
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Category::query();

        // Include meal count
        if ($request->boolean('with_meals_count')) {
            $query->withCount('meals');
        }

        // Include meals
        if ($request->boolean('with_meals')) {
            $query->with('meals');
        }

        $category = $query->findOrFail($id);

        return response()->json([
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Get category by slug.
     */
    public function getBySlug(Request $request, string $slug): JsonResponse
    {
        $query = Category::where('slug', $slug);

        // Include meal count
        if ($request->boolean('with_meals_count')) {
            $query->withCount('meals');
        }

        // Include meals
        if ($request->boolean('with_meals')) {
            $query->with('meals');
        }

        $category = $query->firstOrFail();

        return response()->json([
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $category->name) {
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Check if category has meals
        if ($category->meals()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated meals'
            ], 422);
        }

        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Upload category image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $path = $request->file('image')->store('categories', 'public');

        return response()->json([
            'message' => 'Image uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => config('app.url') . Storage::url($path)
            ]
        ]);
    }

    /**
     * Delete category image.
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        if (Storage::disk('public')->exists($request->path)) {
            Storage::disk('public')->delete($request->path);
            
            return response()->json([
                'message' => 'Image deleted successfully'
            ]);
        }

        return response()->json([
            'message' => 'Image not found'
        ], 404);
    }

    /**
     * Get active categories only.
     */
    public function active(): AnonymousResourceCollection
    {
        $categories = Category::where('is_active', true)
            ->withCount('meals')
            ->get();

        return CategoryResource::collection($categories);
    }
}
