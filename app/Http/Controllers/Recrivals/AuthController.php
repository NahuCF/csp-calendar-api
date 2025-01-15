<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\IdentifierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required',  'string'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');

        if (User::query()->where('email', strtolower($email))->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email already on use.'],
            ]);
        }

        $tenantId = (string) Str::uuid();

        Tenant::query()->create([
            'id' => $tenantId,
            'identifier' => IdentifierService::generate(),
        ]);

        $user = User::create([
            'email' => strtolower($email),
            'tenant_id' => $tenantId,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('Guest');
        $role = Role::where('name', 'Guest')->first();
        $user->syncPermissions($role->permissions);

        $user->api_token = $user->createToken('api-token')->plainTextToken;

        $user->load('tenant', 'roles');

        return UserResource::make($user);
    }
}
