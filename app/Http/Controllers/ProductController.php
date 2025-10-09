<?php

namespace App\Http\Controllers;

use App\enum\ProductAdminStatus;
use App\Http\Resources\ProductRessource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $Product = Product::with(['categorie', 'unite'])
            ->where(Product::COL_USER_ID, Auth::id())
            ->orderby(Product::COL_STATUS)
            ->orderby(Product::COL_ID, 'desc')
            ->get();

        return response()
            ->json(ProductRessource::collection($Product));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
             Product::COL_NAME => 'required',
             Product::COL_DESCRIPTION => 'required',
             Product::COL_PRICE => 'required',
             Product::COL_QUANTITY => 'required',
             Product::COL_CATEGORIE_ID => 'required',
             Product::COL_UNITE_ID => 'required',
          ]);

        Auth::user()->products()->create($validated);

        return response()
            ->json(['message' => 'Product created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Product = Product::with(['categorie', 'unite'])
            ->where(Product::COL_ID, $id)
            ->first();

        // Get 4 related products from the same category
        $relatedProducts = Product::where(Product::COL_CATEGORIE_ID, $Product->categorie_id)
            ->where(Product::COL_ID, '!=', $id) // Exclude current product
            ->where(Product::COL_AVAILABILITY_STATUS, ProductAdminStatus::Published->value)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // If no related products found, get 4 random products
        if ($relatedProducts->count() < 4) {
            $relatedProducts = Product::where(Product::COL_ID, '!=', $id) // Exclude current product
                ->where(Product::COL_AVAILABILITY_STATUS, ProductAdminStatus::Published->value)
                ->inRandomOrder()
                ->limit(3)
                ->get();
        }

        // Add related products as an attribute to the main product
        $Product->relatedProducts = $relatedProducts;

        return response()
            ->json(new ProductRessource($Product));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function GetProduct(Request $request)
    {

        $Product = Product::with(['categorie', 'unite'])
            ->where(Product::COL_AVAILABILITY_STATUS, ProductAdminStatus::Published->value)
            ->orderby(Product::COL_STATUS)
            ->get();

        return response()
            ->json(ProductRessource::collection($Product));
    }
}
