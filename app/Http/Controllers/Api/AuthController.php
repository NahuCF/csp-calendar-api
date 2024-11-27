<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = (object) [
            'login' => $request->login,
            'password' => $request->password,
        ];

        $result = $this->authService->attemptLogin($credentials);

        if (! $result->success) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => new UserResource($result->user),
            'apiToken' => $result->token,
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request);

        return response()->json([
            'message' => 'Registered successfully',
            'user' => new UserResource($result->user),
            'apiToken' => $result->token,
        ], Response::HTTP_CREATED);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
