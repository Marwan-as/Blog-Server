<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authService->login($request);

        if (!$token) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json(['message' => 'Welcome Back!', 'token' => $token->plainTextToken], 200);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register($request);

        if (!$data) {
            return response()->json(['message' => 'Failed to register user.', 500]);
        }

        $token = $data['token'];

        $user = $data['user'];

        return response()->json(['message' => 'Welcome!', 'token' => $token->plainTextToken, 'user' => $user], 200);
    }
}
