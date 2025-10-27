<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RewardController extends Controller
{
    // List all rewards (admin)
    public function index()
    {
        return response()->json(Reward::with('user')->orderByDesc('earned_at')->paginate(20));
    }

    // Show a single reward
    public function show($id)
    {
        $reward = Reward::with('user')->findOrFail($id);
        return response()->json($reward);
    }

    // Create a reward (admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'value' => 'required|numeric',
            'title' => 'required|string',
            'description' => 'required|string',
            'earned_at' => 'required|date',
            'is_used' => 'boolean',
            'expires_at' => 'nullable|date',
            'used_order_id' => 'nullable|exists:orders,id',
            'discount_applied' => 'nullable|numeric',
            'conditions' => 'nullable|array',
        ]);
        $reward = Reward::create($data);
        return response()->json($reward, 201);
    }

    // Update a reward (admin)
    public function update(Request $request, $id)
    {
        $reward = Reward::findOrFail($id);
        $data = $request->validate([
            'type' => 'string',
            'value' => 'numeric',
            'title' => 'string',
            'description' => 'string',
            'is_used' => 'boolean',
            'expires_at' => 'nullable|date',
            'used_order_id' => 'nullable|exists:orders,id',
            'discount_applied' => 'nullable|numeric',
            'conditions' => 'nullable|array',
        ]);
        $reward->update($data);
        return response()->json($reward);
    }

    // Delete a reward (admin)
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);
        $reward->delete();
        return response()->json(['message' => 'Reward deleted']);
    }

    // List rewards for the authenticated user
    public function myRewards()
    {
        $user = auth()->user();
        $rewards = Reward::where('user_id', $user->id)->orderByDesc('earned_at')->get();
        return response()->json($rewards);
    }
}
