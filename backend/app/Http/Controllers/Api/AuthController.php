<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Info(title="Singre API", version="1.0.0")
     * @OA\SecurityScheme(
     *   securityScheme="bearerAuth",
     *   type="http",
     *   scheme="bearer",
     *   bearerFormat="JWT"
     * )
     */
    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   tags={"Auth"},
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(required={"name","email","password"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'password' => Hash::make($request->validated()['password']),
        ]);

        return response()->json([
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(required={"email","password"},
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Simple throttle: allow up to 5 attempts per minute per IP
        $key = 'login:'.request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return response()->json(['message' => 'Too many attempts. Try again in '.$seconds.' seconds.'], 429);
        }

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($key);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me",
     *   tags={"Auth"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function me(): JsonResponse
    {
        return response()->json(['user' => auth()->user()]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function logout(): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }
}
