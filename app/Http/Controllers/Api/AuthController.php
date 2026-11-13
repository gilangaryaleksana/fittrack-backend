<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $user = User::create($data); // password hashed via model cast

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', // email or username
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['login'])
            ->orWhere('username', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/username atau password salah'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'height_cm' => 'sometimes|nullable|numeric|between:50,300',
            'weight_kg' => 'sometimes|nullable|numeric|between:20,500',
            'birth_date' => 'sometimes|nullable|date|before_or_equal:today',
            'gender' => 'sometimes|nullable|in:male,female,other',
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        DB::transaction(function () use ($data, $user) {
            $user->update(['password' => $data['new_password']]);

            $currentToken = $user->currentAccessToken();
            $tokens = $user->tokens();

            if ($currentToken instanceof PersonalAccessToken) {
                $tokens->whereKeyNot($currentToken->getKey());
            }

            $tokens->delete();
        });

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function deleteAccount(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        DB::transaction(function () use ($user) {
            DB::table('clubs')->where('created_by', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}
