<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Draft;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Nette\Utils\FileSystem;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isEmpty;

class PostController extends Controller
{

    private $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function getPosts()
    {
        $posts = Post::with('user')->latest()->where('privacy', 'public')->get();

        return response()->json(['posts' => $posts], 200);
    }

    public function getPost(Post $post)
    {
        $post = $this->postService->getPost($post);
        
        return response()->json(['post' => $post], 200);
    }

    public function getPostComments(Post $post)
    {
        $comments = $this->postService->getPostComments($post);

        return response()->json(['comments' => $comments], 200);
    }

    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request);

        if (!$post) {
            return response()->json(['message' => 'Failed to create post. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Post created successfully!', 'post' => $post], 200);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $updated = $this->postService->updatePost($request, $post);

        if (!$updated) {
            return response()->json(['message' => 'Failed to update post. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Post updated successfully!', 'post' => $post->refresh()], 200);
    }

    public function destroy(Post $post)
    {
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $deleted = $this->postService->deletePost($post);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete post. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Post deleted successfully!'], 200);
    }
}
