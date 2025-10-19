<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Role constants
    const ROLE_ADMIN = 1;
    const ROLE_CUISINIER = 2;
    const ROLE_LIVREUR = 3;
    const ROLE_CLIENT = 4;

    /**
     * Display a listing of users (Admin only)
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== self::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = User::query();

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user (Admin only)
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== self::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'integer', Rule::in([
                self::ROLE_ADMIN,
                self::ROLE_CUISINIER,
                self::ROLE_LIVREUR,
                self::ROLE_CLIENT
            ])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->first_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, User $user)
    {
        $authUser = $request->user();

        // Admin can view any user, others can only view themselves
        if ($authUser->role !== self::ROLE_ADMIN && $authUser->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $authUser = $request->user();

        // Admin can update any user, others can only update themselves (except role)
        if ($authUser->role !== self::ROLE_ADMIN && $authUser->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $rules = [
            'first_name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];

        // Only admin can update role
        if ($authUser->role === self::ROLE_ADMIN) {
            $rules['role'] = ['sometimes', 'required', 'integer', Rule::in([
                self::ROLE_ADMIN,
                self::ROLE_CUISINIER,
                self::ROLE_LIVREUR,
                self::ROLE_CLIENT
            ])];
        } elseif ($request->has('role')) {
            return response()->json(['message' => 'You cannot change your role'], 403);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['first_name', 'email', 'phone', 'address']);

        if ($request->has('first_name')) {
            $data['last_name'] = $request->first_name;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($authUser->role === self::ROLE_ADMIN && $request->has('role')) {
            $data['role'] = $request->role;
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user (Admin only)
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== self::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
