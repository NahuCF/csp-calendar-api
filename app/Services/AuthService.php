<?php

namespace App\Services;

use App\DataTransferObjects\AuthResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function createToken(object $user): string
    {
        $user->tokens()->delete();

        $plainTextToken = $user->createToken('apiToken')->plainTextToken;

        return $plainTextToken;
    }

    public function attemptLogin(object $credentials): AuthResult
    {
        $isEmail = filter_var($credentials->login, FILTER_VALIDATE_EMAIL);
        $credential = $isEmail ? 'email' : 'phone';

        $user = User::where($credential, $credentials->login)->first();

        if (! $user || ! Hash::check($credentials->password, $user->password)) {
            return new AuthResult(false);
        }

        $token = $this->createToken($user);

        return new AuthResult(true, $user, $token);
    }

    public function register(object $data): AuthResult
    {
        $tenantId = $data->tenant_id ?? (string) Str::uuid();

        $user = User::create([
            'email' => $data->email ?? null,
            'password' => Hash::make($data->password),
            'tenant_id' => $tenantId,
        ]);
        $user->assignRole('Admin');
        $user->permissions = $user->getAllPermissions()->pluck('name');

        $token = $this->createToken($user);

        return new AuthResult(true, $user, $token);
    }

    public function logout(): void
    {
        $user = request()->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }
    }
}
