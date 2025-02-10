<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;


class AuthService
{
    public function login(LoginRequest $request): ?object
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            throw new Exception('Invalid Credentials.', 400);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken('ACCESS_TOKEN' . $user->id);

        return $token;
    }

    public function logout(Request $request): ?bool
    {
        $user = $request->user();

        return $user->tokens()->delete();
    }

    public function register(RegisterRequest $request): ?array
    {
        $data = $request->validated();

        $data['password'] = bcrypt($data['password']);

        if ($request->isAdmin) {
            $data['isAdmin'] = true;
        }

        $user = DB::transaction(fn() => User::create($data));

        if (!$user) {
            throw new InvalidArgumentException('Failed to register user.', 500);
        }

        Auth::login($user);

        $token = $user->createToken('ACCESS_TOKEN' . $user->id);

        return ['token' => $token, 'user' => $user];
    }
}
