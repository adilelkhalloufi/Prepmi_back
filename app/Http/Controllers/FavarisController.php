<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductRessource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavarisController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favorites;

        return response()
            ->json(ProductRessource::collection($favorites));

    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $user->favorites()->toggle($request->product_id);

        // if the product was added to favorites said "Product added to favorites"
        // if the product was removed from favorites said "Product removed from favorites"
        return response()->json([
            'message' => $user->favorites()->where('product_id', $request->product_id)->exists()
                ? 'Product added to favorites'
                : 'Product removed from favorites',
        ]);
    }
}
