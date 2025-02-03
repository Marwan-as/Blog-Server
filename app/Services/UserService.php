<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use InvalidArgumentException;

class UserService
{
    public function getUsers()
    {
        return User::latest()->where('isAdmin', 0)->get();
    }

    public function getUserPosts(User $user, $all = false)
    {
        if (!$user) {
            throw new InvalidArgumentException('User is not provided.', 400);
        }

        // $posts = Post::latest()->where('user_id', $user->id)->get();
        if ($all) {
            $posts = $user->posts()->with('user')->latest()->get()->loadCount('comments');
            return $posts;
        }

        $posts = $user->posts()->with('user')->latest()->where('privacy', 'public')->get()->loadCount('comments');
        return $posts;
    }

    public function getUserDrafts(User $user)
    {
        if (!$user) {
            throw new InvalidArgumentException('User is not provided.', 400);
        }

        $drafts = $user->drafts()->with('user')->latest()->get();

        return $drafts;
    }

    public function getUserComments(User $user)
    {
        if (!$user) {
            throw new InvalidArgumentException('User is not provided.', 400);
        }

        $comments = $user->comments()->with('user', 'post')->latest()->get();

        return $comments;
    }
}
