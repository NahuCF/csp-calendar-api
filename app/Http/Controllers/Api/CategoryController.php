<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->validate([
            'paginated' => ['sometimes'],
            'search' => ['sometimes'],
        ]);

        $paginated = data_get($input, 'paginated', false);
        $search = data_get($input, 'search');

        $user = Auth::user();

        $clients = Category::query()
            ->withCount('events')
            ->where('tenant_id', $user->tenant_id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'asc');

        $clients = $paginated ? $clients->paginate(15) : $clients->get();

        return CategoryResource::collection($clients);
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $color = data_get($input, 'color');

        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        $client = Category::query()
            ->create([
                'name' => $name,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'color' => $color,
            ]);

        $client->load('user');

        return CategoryResource::make($client);
    }

    public function update(Request $request, Category $category)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $name = data_get($input, 'name');
        $color = data_get($input, 'color');

        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        if ($user->tenant_id !== $category->tenant_id) {
            throw ValidationException::withMessages([
                'category' => 'Category not found',
            ]);
        }

        $category->update([
            'name' => $name,
            'color' => $color,
        ]);

        $category->load('user');

        return CategoryResource::make($category);
    }

    public function destroy(Category $category)
    {
        $user = Auth::user();

        if ($category->tenant_id != $user->tenant_id) {
            throw ValidationException::withMessages([
                'cannot_delete' => ['Cannot delete this category'],
            ]);
        }

        $category->delete();
    }

    public function destroyBulk(Request $request)
    {
        $input = $request->validate([
            'ids' => ['required', 'array'],
        ]);

        $ids = data_get($input, 'ids');
        $user = Auth::user();

        Category::query()
            ->whereIn('id', $ids)
            ->where('tenant_id', $user->tenant_id)
            ->delete();

        return response()->noContent();
    }
}
