<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // list all bids for this user
        $bids = Bid::where('user_id', auth()->id())
            ->get();

        return response()->json($bids);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // add new bid
        $request->validate([
            'price' => 'required',
            'product_id' => 'required',
        ]);

        $bid = $this
            ->auth()
            ->user()
            ->bids()
            ->create($request->validated());

        return response()->json($bid);

    }

    /**
     * Display the specified resource.
     */
    public function show(Bid $bid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bid $bid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bid $bid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bid $bid)
    {
        // delete bid
        $bid->delete();

        return response()->json([
            'message' => 'Bid deleted successfully',
        ]);
    }
}
