<?php

namespace App\Services;

use App\Models\Post;
use InvalidArgumentException;

class PostService
{
    public function getPost(Post $post)
    {
        return Post::with('user')->findOrFail($post->id);
    }

    public function getPostComments(Post $post)
    {
        return $post->loadCount('comments')->comments()->with('user', 'post')->latest()->get();
    }
}
