<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->ativo) {
            Auth::logout();
            return response()->json(['message' => 'Usuário inativo.'], 403);
        }

        $token = $user->createToken($request->device_name ?? 'api-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $this->userResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->userResource($request->user()));
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'perfil' => $user->perfil,
            'phone' => $user->phone,
            'cpf' => $user->cpf,
            'ativo' => $user->ativo,
            'supervisor_id' => $user->supervisor_id,
        ];
    }
}

