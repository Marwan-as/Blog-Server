<?php

namespace App\Services;

use App\Models\Comment;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommentNotification;
use App\Http\Requests\Comment\StoreCommentRequest;
use Exception;

class CommentService
{
    public function createComment(StoreCommentRequest $request): Comment
    {
        $data = $request->validated();

        $user = $request->user();
        $data['user_id'] = $user->id;

        $comment = DB::transaction(fn() => Comment::create($data));

        if (!$comment) {
            throw new Exception('Failed to create comment.', 500);
        }

        $postOwner = $comment->post->user;
        if ($postOwner->id !== $user->id) {
            $postOwner->notify(new CommentNotification($comment, $user));
        }

        return $comment;
    }

    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }
}
