<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): LoginResource|JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $result = $this->authService->login($credentials);

        if (!$result) {
            return response()->json(['errors' => 'Email or password is incorrect'], 401);
        }

        return new LoginResource($result);
    }

    public function logout(): JsonResponse
    {
        $status = $this->authService->logout();

        if (!$status) {
            return response()->json([
                'errors' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'data' => 'OK',
        ]);
    }

    public function refresh(): LoginResource
    {
        return new LoginResource($this->authService->refresh());
    }
}
