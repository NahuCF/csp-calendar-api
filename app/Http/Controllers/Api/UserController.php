<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'search' => ['sometimes'],
        ]);

        $user = Auth::user();
        $search = data_get($input, 'search');

        $users = User::query()
            ->with('roles')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
            ->where('id', '!=', $user->id)
            ->orderBy('id', 'asc')
            ->paginate(15);

        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required',  'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_ids' => ['required'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $firstName = data_get($input, 'first_name');
        $lastName = data_get($input, 'last_name');
        $roleIds = data_get($input, 'role_ids');

        $user = Auth::user();

        $newUser = User::create([
            'email' => strtolower($email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make($password),
            'password_plain_text' => $password,
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
        ]);

        $roles = Role::whereIn('id', $roleIds)->get();

        $newUser->assignRole($roles->pluck('name'));

        return new UserResource($newUser);
    }

    public function me()
    {
        $user = Auth::user();
        $user->permissions = $user->getAllPermissions()->pluck('name')->unique();

        return new UserResource($user);
    }

    public function emails()
    {
        $user = Auth::user();

        return response()->json([
            'data' => User::where('tenant_id', $user->tenant_id)->pluck('email'),
        ]);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $input = $request->validate([
            'email' => ['sometimes', 'string', 'email', 'max:255'],
            'password' => ['sometimes',  'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_ids' => ['sometimes'],
            'avatar' => ['sometimes', 'file', 'max:2048'],
            'timezone_id' => ['sometimes'],
            'identifier' => ['sometimes'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $firstName = data_get($input, 'first_name');
        $lastName = data_get($input, 'last_name');
        $roleIds = data_get($input, 'role_ids');
        $timezoneId = data_get($input, 'timezone_id');
        $avatar = $request->file('avatar');
        $identifier = data_get($input, 'identifier');

        $existUserWithSameEmail = User::query()
            ->where('email', strtolower($email))
            ->where('id', '!=', $user->id)
            ->exists();

        if ($existUserWithSameEmail) {
            throw ValidationException::withMessages([
                'email' => ['Email already on use.'],
            ]);
        }

        if ($identifier) {
            $existIdentifier = Tenant::query()
                ->where('id', '!=', $user->tenant_id)
                ->where('identifier', $identifier)
                ->exists();

            if ($existIdentifier) {

                throw ValidationException::withMessages([
                    'identifier' => ['Identifier already on use.'],
                ]);
            }

            Tenant::query()
                ->where('id', $user->tenant_id)
                ->update(['identifier' => $identifier]);
        }

        $fields = [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        if ($avatar) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $name = now()->timestamp.'-'.$avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $name, 'public');

            $fields['avatar'] = $name;
            $fields['avatar_path'] = $path;
        }

        if ($email) {
            $fields['email'] = $email;
        }

        if ($password) {
            $fields['password'] = Hash::make($password);
        }

        $user->update($fields);

        if ($roleIds) {
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($roles->pluck('name'));
        }

        if ($timezoneId && $user->can('Edit Timezone')) {
            Tenant::query()
                ->where('id', $user->tenant_id)
                ->update(['timezone_id' => $timezoneId]);
        }

        $user->load('tenant');

        return new UserResource($user);
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'user_ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'user_ids');

        User::whereIn('id', $ids)->delete();

        return response()->noContent();
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
