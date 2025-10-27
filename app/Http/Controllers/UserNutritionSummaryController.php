<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserNutritionSummary;

class UserNutritionSummaryController extends Controller
{
    /**
     * Get the authenticated user's nutrition summary (last 7 days).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $summaries = UserNutritionSummary::where('user_id', $user->id)
            ->get();

        return response()->json($summaries);
    }
}
