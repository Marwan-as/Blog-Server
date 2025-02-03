<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'privacy' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4|max:30720',
        ]);

        //auth:sanctum => bearer token authorization header
        $user = $request->user();
        $data['user_id'] = $user->id;

        $imageStatus = $request->imageStatus;

        if ($imageStatus && $request->draftId) {
            $draft = Draft::find($request->draftId);

            if ($draft) {
                switch ($imageStatus) {
                    case "removed":
                        if ($draft->imagePath) {
                            $relativePath_draft = FileHelper::getRelativeFilePath($draft->imagePath);
                            $this->deleteFile($relativePath_draft, 'public');
                        }

                        break;

                    case "noChange":
                        if ($draft->imagePath) {
                            $relativePath_draft = FileHelper::getRelativeFilePath($draft->imagePath);
                            $new_path = $this->moveFile($relativePath_draft, 'uploads/posts', 'public');
                            $data['imagePath'] = $new_path;
                        }

                        break;

                    case "changed":
                        if ($request->hasFile('image') && $draft->imagePath) {
                            $image = $request->file('image');
                            $path = $this->storeFile($image, 'uploads/posts', 'public');
                            $data['imagePath'] = $path;
                        }
                        break;

                    case "added":
                        if ($request->hasFile('image')) {
                            $image = $request->file('image');
                            $path = $this->storeFile($image, 'uploads/posts', 'public');
                            $data['imagePath'] = $path;
                        }

                        break;
                }
            }
        } else if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $this->storeFile($image, 'uploads/posts', 'public');
            $data['imagePath'] = $path;
        }


        //User didn't select privacy option => privacy = public
        if ($data['privacy'] === 'undefined') {
            $data['privacy'] = 'public';
        }

        //create post
        $post = DB::transaction(function () use ($data) {
            return Post::create($data);
        });

        if (!$post) {
            return response()->json(['message' => 'Failed to create post. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Post created successfully!', 'post' => $post], 200);
    }

    public function update(Request $request, Post $post)
    {
        Log::info('All Request Data:', [$request->all()]);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'privacy' => 'sometimes|required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,mp4|max:30720',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $relativePath = FileHelper::getRelativeFilePath($post->imagePath);
            // $type = $image->getClientMimeType();
            $data['imagePath'] = $this->storeFile($image, 'uploads/posts', 'public', $relativePath);
        }

        $updated = DB::transaction(function () use ($post, $data) {
            return $post->update($data);
        });

        if (!$updated) {
            return response()->json(['message' => 'Failed to update post. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Post updated successfully!', 'post' => $post->refresh()], 200);
    }

    public function delete(Post $post)
    {
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }
        Log::info('hasImage', [$post->imagePath]);


        if (!$post->imagePath) {
            $post->delete();
            return response()->json(['message' => 'Post deleted successfully!'], 200);
        }

        $relativePath = FileHelper::getRelativeFilePath($post->imagePath);
        $deleted = $this->deleteFile($relativePath,  'public');

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete post. Please try again later.'], 500);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully!'], 200);
    }
}
