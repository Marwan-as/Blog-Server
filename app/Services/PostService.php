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
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;


        if ($request->imageStatus && $request->draftId) {
            $draft = Draft::find($request->draftId);
            $data['imagePath'] = $this->handleImageForDraft($request, $draft);
        } elseif ($request->hasFile('image')) {
            $data['imagePath'] = $this->storeFile($request->file('image'), 'uploads/posts', 'public');
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
            $data['imagePath'] = $this->storeFile($request->file('image'), 'uploads/posts', 'public', $relativePath);
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

    private function handleImageForDraft(StorePostRequest $request, ?Draft $draft): ?string
    {
        if (!$draft) {
            return null;
        }

        switch ($request->imageStatus) {
            case "removed":
                if ($draft->imagePath) {
                    $this->deleteFile(FileHelper::getRelativeFilePath($draft->imagePath), 'public');
                }
                return null;

            case "noChange":
                return $draft->imagePath
                    ? $this->moveFile(FileHelper::getRelativeFilePath($draft->imagePath), 'uploads/posts', 'public')
                    : null;

            case "changed":
            case "added":
                return $request->hasFile('image')
                    ? $this->storeFile($request->file('image'), 'uploads/posts', 'public')
                    : null;
        }

        return null;
    }
}
