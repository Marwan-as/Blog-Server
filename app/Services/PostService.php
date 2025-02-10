<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Draft;
use App\Helpers\FileHelper;
use InvalidArgumentException;
use App\Traits\FileServiceTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PostService
{
    use FileServiceTrait;

    public function getPost(Post $post): Post
    {
        return Post::with('user')->findOrFail($post->id);
    }

    public function getPostComments(Post $post): Collection
    {
        return $post->loadCount('comments')->comments()->with('user', 'post')->latest()->get();
    }

    public function createPost(StorePostRequest $request): ?Post
    {
        Log::info('request', [$request->all()]);
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $imageStatus = $request->imageStatus;

        if ($imageStatus && $request->draftId) {
            $draft = Draft::find($request->draftId);

            if ($draft) {
                switch ($imageStatus) {
                    case "removed":
                        if ($draft->imagePath) {
                            $relativePath_draft = FileHelper::getRelativeFilePath($draft->imagePath);
                            $this->deleteFile($relativePath_draft, 'public');
                            $draft->delete();
                        }

                        break;

                    case "noChange":
                        if ($draft->imagePath) {
                            $data['imagePath'] = $draft->imagePath;
                            $draft->delete();
                        }

                        break;

                    case "changed":
                        if ($request->hasFile('image') && $draft->imagePath) {
                            $image = $request->file('image');
                            $path = $this->storeFile($image, 'uploads/media', 'public');
                            $data['imagePath'] = $path;
                            $draft->delete();
                        }
                        break;

                    case "added":
                        if ($request->hasFile('image')) {
                            $image = $request->file('image');
                            $path = $this->storeFile($image, 'uploads/media', 'public');
                            $data['imagePath'] = $path;
                            $draft->delete();
                        }

                        break;
                }
            }
        } else if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $this->storeFile($image, 'uploads/media', 'public');
            $data['imagePath'] = $path;
        }

        $data['privacy'] = $data['privacy'] === 'undefined' ? 'public' : $data['privacy'];

        $post = DB::transaction(fn() => Post::create($data));

        if (!$post) {
            throw new Exception('Failed to create post.', 500);
        }

        return $post;
    }

    public function updatePost(UpdatePostRequest $request, Post $post): ?Post
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $relativePath = FileHelper::getRelativeFilePath($post->imagePath);
            $data['imagePath'] = $this->storeFile($request->file('image'), 'uploads/media', 'public', $relativePath);
        }

        DB::transaction(fn() => $post->update($data));

        return $post;
    }

    public function deletePost(Post $post): ?bool
    {
        if (!$post->imagePath) {
            return $post->delete();
        }

        $relativePath = FileHelper::getRelativeFilePath($post->imagePath);
        $deleted = $this->deleteFile($relativePath, 'public');

        if (!$deleted) {
            throw new Exception('Failed to delete post image.');
        }

        return $post->delete();
    }
}
