<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommentNotification;
use App\Services\CommentService;

class CommentController extends Controller
{
    private $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(StoreCommentRequest $request)
    {

        $comment =  $this->commentService->createComment($request);

        if (!$comment) {
            return response()->json(['message' => 'Failed to add comment!'], 500);
        }

        return response()->json(['message' => 'Comment Added!', 'comment' => $comment], 200);
    }

    public function destroy(Comment $comment)
    {
        $deleted = $this->commentService->deleteComment($comment);
        
        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete comment.', 500]);
        }

        return response()->json(['message' => 'Comment deleted!', 200]);
    }
}
