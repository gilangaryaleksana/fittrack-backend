<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const PUBLIC_FIELDS = ['id', 'name', 'username', 'avatar', 'bio', 'city', 'country'];

    public function index(Request $request)
    {
        $q = User::select(self::PUBLIC_FIELDS);

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }

        return response()->json($q->orderBy('name')->paginate($request->integer('per_page', 20)));
    }

    public function show($id)
    {
        $user = User::withCount([
            'activities',
            'followers as followers_count' => fn ($q) => $q->where('follows.status', 'accepted'),
            'following as following_count' => fn ($q) => $q->where('follows.status', 'accepted'),
        ])->findOrFail($id);

        return response()->json($user->only([...self::PUBLIC_FIELDS, 'weight', 'height'])
            + $user->only(['activities_count', 'followers_count', 'following_count']));
    }

    public function update(Request $request, $id)
    {
        abort_unless((int) $id === (int) $request->user()->id, 403, 'Hanya bisa edit profil sendiri');

        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:8',
            'avatar' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(Request $request, $id)
    {
        abort_unless((int) $id === (int) $request->user()->id, 403, 'Hanya bisa hapus akun sendiri');

        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}
