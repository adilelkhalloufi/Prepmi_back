<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\enum\ProfilStatus;
use App\enum\UserRole;
use App\Mail\CodeVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! auth()->attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();

        if ($user->status != ProfilStatus::ACTIF->value) {
            return response([
                'message' => 'Your account is inactive',
            ], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'favoris' => $user->favorites,
            'token' => $token,
        ]);
    }

    public function register(
        Request $request,
        CreateUser $createUser
    ) {
        $request->validate([
            'password' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'agreement' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',

        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json([
                'message' => 'Email already exists',
            ], 409);
        }
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'password' => bcrypt($request->password),
            'role' => UserRole::CLIENT->value,
            'status' => ProfilStatus::ACTIF->value,
        ]);

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(auth()->user());
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $user->update($request->all());

        return response()->json($user);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json($user);
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response([
                'message' => 'User not found',
            ], 404);
        }

        $code = rand(1000, 9999);

        $user->update([
            'code_verify' => $code,
        ]);

        Mail::to($user->email)->send(new CodeVerification($user));

        return response()->json([
            'message' => 'Code sent to your email',
        ]);
    }

    public function verifyCodeResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('code_verify', $request->code)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid code or email'], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'code_verify' => null,
        ]);

        return response()->json(['message' => 'Password reset successful']);
    }

    public function TotalPointsEarned(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'total_points_earned' => $user->total_points_earned,
        ]);
    }
}
