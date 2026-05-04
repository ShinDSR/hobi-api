<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function login(array $credentials): ?array
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        return $this->formatTokenResponse($token);
    }

    public function logout(): bool
    {
        try {
            Auth::guard('api')->logout();
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    public function refresh(): array
    {
        $newToken = Auth::guard('api')->refresh();
        return $this->formatTokenResponse($newToken);
    }

    protected function formatTokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60 * 24 * 7,
            'user' => Auth::guard('api')->user()
        ];
    }
}
