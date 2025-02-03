<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommentNotification;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'body' => 'required|max:255',
            'post_id' => 'required|exists:posts,id'
        ]);

        $user = $request->user();
        $data['user_id'] = $user->id;

        $comment = DB::transaction(function () use ($data) {
            return Comment::create($data);
        });

        if (!$comment) {
            return response()->json(['message' => 'Failed to add comment!'], 500);
        }


        $postOwner = $comment->post->user;
        if ($postOwner->id !== $user->id) {
            $postOwner->notify(new CommentNotification($comment, $user));
        }

        return response()->json(['message' => 'Comment Added!', 'comment' => $comment], 200);
    }

    public function delete(Comment $comment)
    {
        $deleted = $comment->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete comment.', 500]);
        }

        return response()->json(['message' => 'Comment deleted!', 200]);
    }
}
