<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class MediaService
{
    public function getMedia(): Collection
    {
        return Post::select(['id', 'title', 'imagePath', 'user_id'])
            ->with(['user:id, name, email'])
            ->where('privacy', 'public')
            ->latest()
            ->get()
            ->transform(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'imagePath' => $post->imagePath,
                    'user' => optional($post->user)->only(['id', 'name', 'email']),
                ];
            });
    }
}
