<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/login -> returns a Sanctum token.
    public function login(ApiLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Hash::check compares the typed password with the stored hash.
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'role' => $user->role,
            ],
            'message' => 'Login successful.',
        ], 200);
    }

    // POST /api/logout -> deletes the token that made this request.
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Logged out.',
        ], 200);
    }
}
