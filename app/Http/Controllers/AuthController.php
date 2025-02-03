<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{

    public function getAbilities()
    {
        $abilities = ['Manage Posts' => 'manage-posts', 'Manage Comments' => 'manage-comments', 'Manage Users' => 'manage-users'];
        return response()->json(['abilities' => $abilities], 200);
    }
    
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);



        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken('ACCESS_TOKEN' . $user->id);

        return response()->json(['message' => 'Welcome Back!', 'token' => $token->plainTextToken], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => ['confirmed', Password::min(8)->letters()],
        ]);


        $data['password'] = bcrypt($data['password']);
        // $abilities = [];
        if ($request->isAdmin) {
            $data['isAdmin'] = true;
            // if (!$request->abilities) {
            //     $abilities = ['manage-posts', 'manage-comments', 'manage-users'];
            // } else {
            //     $abilities = $request->abilities;
            // }
        }

        $user = DB::transaction(function () use ($data) {
            return User::create($data);
        });

        Auth::login($user);


        $token = $user->createToken('ACCESS_TOKEN' . $user->id);

        return response()->json(['message' => 'Welcome!', 'token' => $token->plainTextToken, 'user' => $user], 200);
    }
}
