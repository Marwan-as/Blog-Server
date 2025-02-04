<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $userService;

    /**
     * __construct
     *
     * @param  mixed $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUsers()
    {
        $users = $this->userService->getUsers();

        return response()->json(['users' => $users], 200);
    }

    public function getCurrentUser(Request $request)
    {
        $user = $request->user();

        return response()->json(['user' => $user], 200);
    }

    public function getCurrentUserPosts(Request $request)
    {
        $user = $request->user();

        $posts = $this->userService->getUserPosts($user, true);

        return response()->json(['posts' => $posts], 200);
    }

    public function getCurrentUserComments(Request $request)
    {
        $user = $request->user();

        $comments = $this->userService->getUserComments($user);

        return response()->json(['comments' => $comments], 200);
    }

    public function getCurrentUserDrafts(Request $request)
    {
        $user = $request->user();

        $drafts = $this->userService->getUserDrafts($user);

        return response()->json(['drafts' => $drafts], 200);
    }

    public function getUser(User $user)
    {
        return response()->json(['user' => $user], 200);
    }

    public function getUserPosts(User $user)
    {
        $posts = $this->userService->getUserPosts($user);

        return response()->json(['posts' => $posts], 200);
    }

    public function getUserDrafts(User $user)
    {
        $drafts = $this->userService->getUserDrafts($user);

        return response()->json(['drafts' => $drafts], 200);
    }

    public function getUserComments(User $user)
    {
        $comments = $this->userService->getUserComments($user);

        return response()->json(['comments' => $comments], 200);
    }


    public function update(UpdateUserRequest $request, User $user)
    {
        $updated = $this->userService->updateUser($request, $user);

        if (!$updated) {
            return response()->json(['message' => 'Failed to update profile. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user->refresh()], 200);
    }

    public function destroy(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $deleted = $this->userService->deleteUser($user);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete user. Please try again later.'], 500);
        }

        return response()->json(['message' => 'User deleted successfully!'], 200);
    }
}
