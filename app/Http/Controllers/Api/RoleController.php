<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'roles_from' => ['required', 'string'],
        ]);

        $rolesFrom = data_get($input, 'roles_from');

        $roles = Role::query()
            ->with('permissions')
            ->when($rolesFrom == 'calendar', fn ($query) => $query->where('name', 'like', '-Calendar-%'))
            ->get();

        return response()->json($roles);
    }
}
