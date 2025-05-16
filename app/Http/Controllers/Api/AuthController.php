<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Authenticate user and generate JWT token
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/auth",
     *     summary="Authenticate user and get JWT token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", description="Email or username", example="john.doe123"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="accessToken", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The provided credentials are incorrect.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function authenticate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // Check if username is email or username
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';

        // Find user by email or username
        $user = User::where($field, $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'error' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Create JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'accessToken' => $token
        ]);
    }

    /**
     * Logout user by revoking tokens
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout user and revoke token",
     *     tags={"Auth"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Invalidate the token
        auth()->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }
    
    /**
     * Refresh a token.
     *
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Auth"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="accessToken", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'accessToken' => auth()->refresh()
        ]);
    }
}
