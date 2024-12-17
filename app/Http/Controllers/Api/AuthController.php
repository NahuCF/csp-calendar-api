<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required',  'string'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid credentials'],
            ]);
        }

        $user->tokens()->delete();

        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->api_token = $user->createToken('api-token')->plainTextToken;

        return UserResource::make($user);
    }

    public function register(Request $request)
    {
        $input = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required',  'string'],
            'timezone_id' => ['required'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $firstName = data_get($input, 'first_name');
        $lastName = data_get($input, 'last_name');
        $timezoneId = data_get($input, 'timezone_id');

        if (User::query()->where('email', strtolower($email))->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email already on use.'],
            ]);
        }

        $tenantId = (string) Str::uuid();

        Tenant::query()->create([
            'uuid' => $tenantId,
            'timezone_id' => $timezoneId,
        ]);

        $user = User::create([
            'email' => strtolower($email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'tenant_id' => $tenantId,
            'password' => Hash::make($password),
        ]);
        $user->assignRole('Admin');
        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->api_token = $user->createToken('api-token')->plainTextToken;

        return UserResource::make($user);
    }

    public function logout()
    {
        $user = Auth::user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->noContent();
    }
}
