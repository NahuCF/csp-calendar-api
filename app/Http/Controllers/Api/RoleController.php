<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'search' => ['sometimes'],
        ]);

        $user = Auth::user();

        $search = data_get($input, 'search');

        $roles = Role::query()
            ->with('permissions')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->where(function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                    ->orWhereNull('tenant_id');
            })
            ->orderBy('id', 'asc')
            ->paginate(15);

        return response()->json([
            'data' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permission_ids' => ['required', 'array'],
        ]);

        $name = data_get($input, 'name');
        $permissionIds = data_get($input, 'permission_ids');

        $user = Auth::user();

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $role = Role::create(['name' => $name, 'tenant_id' => $user->tenant_id, 'guard_name' => 'web']);

        $role->syncPermissions($permissions);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permission_ids' => ['required', 'array'],
        ]);

        if ($role->tenant_id == null) {
            throw ValidationException::withMessages([
                'role' => ['You can not update this role'],
            ]);
        }

        $name = data_get($input, 'name');
        $permissionIds = data_get($input, 'permission_ids');

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $role->name = $name;
        $role->save();

        $role->syncPermissions($permissions);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->tenant_id == null) {
            throw ValidationException::withMessages([
                'role' => ['You can not update this role'],
            ]);
        }

        $role->delete();

        return response()->noContent();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'role_ids' => ['required', 'array'],
        ]);

        $roleIds = data_get($input, 'role_ids');
        $user = Auth::user();

        Role::query()
            ->whereIn('id', $roleIds)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
