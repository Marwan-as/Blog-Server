<?php

namespace App\Services;

use App\Models\Post;

class MediaService
{
    public function getMedia()
    {
        return Post::with('user')
            ->where('privacy', 'public')
            ->latest()
            ->get()
            ->makeHidden(['user.password', 'user.remember_token'])
            ->transform(function ($post) {
                return $post->only(['id', 'title', 'imagePath']) + [
                    'user' => $post->user->only(['id', 'name', 'email'])
                ];
            });
    }
}
