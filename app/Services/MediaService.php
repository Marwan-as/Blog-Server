<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class MediaService
{
    public function getMedia(): Collection
    {
        return Post::where('privacy', 'public')
            ->latest()
            ->get()
            ->transform(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'imagePath' => $post->imagePath,
                    'publishedAt' => $post->published_at,
                    'user' => optional($post->user)->only(['id', 'name', 'email']),
                ];
            });
    }
}
