<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCategorieRequest;
use App\Http\Resources\CategoriesResource;
use App\Models\Categorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCategorieRequest $request): JsonResponse
    {

        $categories = Categorie::with('sub_categories')
            ->when($request->type, function ($query) use ($request) {
                return $query->where(Categorie::COL_FAMILY_ID, $request->type);
            })
            ->whereNull(Categorie::COL_PARENT_ID)
            ->orderby('id', 'desc')
            ->get();

        return response()->json(CategoriesResource::collection($categories));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Categorie $categorie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorie $categorie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorie $categorie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorie $categorie)
    {
        //
    }

    //

}
