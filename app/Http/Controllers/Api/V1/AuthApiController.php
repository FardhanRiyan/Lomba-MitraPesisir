<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $this->serializeUser($request->user()),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'whatsapp_number' => $data['whatsapp_number'],
            'password' => $data['password'],
            'role' => UserRole::USER->value,
            'location_id' => $data['location_id'],
            'whatsapp_opt_in' => $data['whatsapp_opt_in'] ?? false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Akun berhasil dibuat.',
            'user' => $this->serializeUser($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Anda sudah keluar.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => $this->serializeUser($request->user()),
        ]);
    }

    public function profile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Data profil berhasil disimpan.',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function password(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return response()->json([
            'message' => 'Kata sandi berhasil diganti.',
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'notif_kondisi' => ['sometimes', 'boolean'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
            'mode_offline' => ['sometimes', 'boolean'],
        ]);

        if ($data === []) {
            return response()->json([
                'message' => 'Tidak ada pengaturan yang diubah.',
            ]);
        }

        $request->user()->update($data);

        return response()->json([
            'message' => 'Pengaturan berhasil disimpan.',
            'user' => $this->serializeUser($request->user()->fresh()),
        ]);
    }

    private function serializeUser(User $user): array
    {
        return $user->load('location')->makeHidden(['password', 'remember_token'])->toArray();
    }
}
