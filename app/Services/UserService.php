<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Helpers\FileHelper;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\User\UpdateUserRequest;
use App\Traits\FileServiceTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    use FileServiceTrait;

    public function getUsers(): Collection
    {
        return User::latest()->where('isAdmin', 0)->get();
    }

    public function getUserPosts(User $user, bool $all = false): Collection
    {
        $query = $user->posts()->with('user')->latest();

        if (!$all) {
            $query->where('privacy', 'public');
        }

        return $query->get()->loadCount('comments');
    }

    public function getUserDrafts(User $user): Collection
    {
        return $user->drafts()->with('user')->latest()->get();
    }

    public function getUserComments(User $user): Collection
    {
        return $user->comments()->with('user', 'post')->latest()->get();
    }

    public function updateUser(UpdateUserRequest $request, User $user): bool
    {
        $data = $request->validated();

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

        $data['showEmail'] = filter_var($request->showEmail, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $updated = DB::transaction(fn() => $user->update($data));

        if (!$updated) {
            throw new Exception('Failed to update user.', 500);
        }

        return $updated;
    }

    public function deleteUser(User $user): bool
    {
        if ($user->profileImagePath) {
            $relativePath_profile = FileHelper::getRelativeFilePath($user->profileImagePath);
            $this->deleteFile($relativePath_profile, 'public');
        }

        if ($user->coverImagePath) {
            $relativePath_cover = FileHelper::getRelativeFilePath($user->coverImagePath);
            $this->deleteFile($relativePath_cover, 'public');
        }

        foreach ($user->posts()->get() as $post) {
            if ($post->imagePath) {
                $relativePath_post = FileHelper::getRelativeFilePath($post->imagePath);
                $this->deleteFile($relativePath_post, 'public');
            }
        }

        foreach ($user->drafts()->get() as $draft) {
            if ($draft->imagePath) {
                $relativePath_draft = FileHelper::getRelativeFilePath($draft->imagePath);
                $this->deleteFile($relativePath_draft, 'public');
            }
        }

        return $user->delete();
    }
}
