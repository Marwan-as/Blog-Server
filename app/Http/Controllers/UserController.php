<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
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

    public function removeProfileImage(Request $request)
    {
        $user = $request->user();
        $path = $user->profileImagePath;
        $relativePath = FileHelper::getRelativeFilePath($path);

        if (!$path) {
            return null;
        }

        $this->deleteFile($relativePath, 'public');
        $user->profileImagePath = null;
        $updated = $user->update();

        if (!$updated) {
            return response()->json(['message', 'Failed to remove profile image. Please try again later.'], 500);
        }

        return response()->json(['message', 'Profile image removed.'], 200);
    }

    public function removeCoverImage(Request $request)
    {
        $user = $request->user();
        $path = $user->coverImagePath;
        $relativePath = FileHelper::getRelativeFilePath($path);

        if (!$path) {
            return null;
        }

        $this->deleteFile($relativePath, 'public');
        $user->coverImagePath = null;


        $updated = $user->update();

        if (!$updated) {
            return response()->json(['message', 'Failed to remove cover image. Please try again later.'], 500);
        }

        return response()->json(['message', 'Cover image removed.'], 200);
    }

    public function update(Request $request, User $user)
    {

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'showEmail' => 'nullable',
            'biography' => 'nullable',
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:30720',
            'coverImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:30720',
        ]);

        $relativePath_cover = FileHelper::getRelativeFilePath($user->coverImagePath);
        $relativePath_profile = FileHelper::getRelativeFilePath($user->profileImagePath);

        if ($request->removeCoverImage && $relativePath_cover) {
            $this->deleteFile($relativePath_cover, 'public');
            $data['coverImagePath'] = null;
        }

        if ($request->removeProfileImage && $relativePath_profile) {
            $this->deleteFile($relativePath_profile, 'public');
            $data['profileImagePath'] = null;
        }

        if ($request->hasFile('profileImage')) {
            $profileImage = $request->file('profileImage');

            $data['profileImagePath'] = $this->storeFile($profileImage, 'uploads/profile_images', 'public', $relativePath_profile);
        }

        if ($request->hasFile('coverImage')) {
            $coverImage = $request->file('coverImage');
            $data['coverImagePath'] = $this->storeFile($coverImage, 'uploads/cover_images', 'public', $relativePath_cover);
        }

        if ($request->showEmail == 'true' || $data['showEmail'] == 'on') {
            $data['showEmail'] = 1;
        } else if ($request->showEmail == 'false' || $data['showEmail'] == 'off') {
            $data['showEmail'] = 0;
        }

        $updated = DB::transaction(function () use ($user, $data) {
            return $user->update($data);
        });

        if (!$updated) {
            return response()->json(['message' => 'Failed to update profile. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user->refresh()], 200);
    }

    public function delete(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $posts = $user->posts();
        $drafts = $user->drafts();

        if ($user->profileImagePath) {
            $realtivePath_profile = FileHelper::getRelativeFilePath($user->profileImagePath);
            $this->deleteFile($realtivePath_profile, 'public');
        }

        if ($user->coverImagePath) {
            $realtivePath_cover = FileHelper::getRelativeFilePath($user->coverImagePath);
            $this->deleteFile($realtivePath_cover, 'public');
        }

        foreach ($posts as $post) {
            if ($post->imagePath) {
                $relativePath_post = FileHelper::getRelativeFilePath($post->imagePath);
                $this->deleteFile($relativePath_post, 'public');
            }
        }

        foreach ($drafts as $draft) {
            if ($draft->imagePath) {
                $relativePath_draft = FileHelper::getRelativeFilePath($draft->imagePath);
                $this->deleteFile($relativePath_draft, 'public');
            }
        }

        if (!$user->delete()) {
            return response()->json(['message' => 'Failed to delete user. Please try again later.'], 500);
        }

        return response()->json(['message' => 'User deleted successfully!'], 200);
    }
}
