<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $firstName = data_get($input, 'first_name');
        $lastName = data_get($input, 'last_name');
        $roleIds = data_get($input, 'role_ids');

        $user = Auth::user();

        $newUser = User::create([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make($password),
            'tenant_id' => $user->tenant_id,
        ]);

        $newUser->assignRole($roleIds);

        return new UserResource($newUser);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user): UserResource
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $firstName = data_get($input, 'first_name');
        $lastName = data_get($input, 'last_name');
        $roleIds = data_get($input, 'role_ids');

        $user->update([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make($password),
        ]);

        $user->syncRoles($roleIds);

        return new UserResource($user);
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
