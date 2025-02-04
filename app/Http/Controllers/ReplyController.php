<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reply\StoreReplyRequest;
use App\Models\Reply;
use App\Notifications\ReplyNotification;
use App\Services\ReplyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    private $replyService;

    public function __construct(ReplyService $replyService)
    {
        $this->replyService = $replyService;
    }

    public function store(StoreReplyRequest $request)
    {
        $reply = $this->replyService->createReply($request);

        if (!$reply) {
            return response()->json(['message' => 'Failed to add reply!'], 500);
        }

        return response()->json(['message' => 'Reply Added!', 'reply' => $reply], 200);
    }

    public function destroy(Reply $reply)
    {
        $deleted = $this->replyService->deleteReply($reply);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete reply.', 500]);
        }

        return response()->json(['message' => 'Reply deleted!', 200]);
    }
}
