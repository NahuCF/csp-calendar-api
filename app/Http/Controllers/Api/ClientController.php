<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $clients = Client::query()
            ->where('tenant_id', $user->tenant_id)
            ->get();

        return ClientResource::collection($clients);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cellphone' => ['required', 'string', 'max:255'],
            'prefix' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $cellphone = data_get($input, 'cellphone');
        $prefix = data_get($input, 'prefix');

        $client = Client::query()
            ->create([
                'name' => $name,
                'cellphone' => $cellphone,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'prefix' => $prefix,
            ]);

        $client->load('user');

        return ClientResource::make($client);
    }
}
