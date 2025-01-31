<?php

namespace App\Http\Controllers\Recrivals;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Models\Sport;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SportController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'identifier' => ['required'],
        ]);

        $identifier = data_get($input, 'identifier');

        $tenant = Tenant::query()
            ->where('identifier', $identifier)
            ->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'identifier' => 'Invalid identifier',
            ]);
        }

        $sports = Sport::query()
            ->where('tenant_id', $tenant->id)
            ->get();

        return SportResource::collection($sports);
    }
}
