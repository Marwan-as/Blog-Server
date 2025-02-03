<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Notifications\ReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'body' => 'required',
            'comment_id' => 'required',
            'post_id' => 'required'
        ]);

        $user = $request->user();
        $data['user_id'] = $user->id;

        $reply = DB::transaction(function () use ($data) {
            return Reply::create($data);
        });

        if (!$reply) {
            return response()->json(['message' => 'Failed to add reply!'], 500);
        }


        $commentOwner = $reply->comment->user;
        if ($commentOwner->id !== $user->id) {
            $commentOwner->notify(new ReplyNotification($reply, $user));
        }

        return response()->json(['message' => 'Reply Added!', 'reply' => $reply], 200);
    }

    public function delete(Reply $reply)
    {
        $deleted = $reply->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete reply.', 500]);
        }

        return response()->json(['message' => 'Reply deleted!', 200]);
    }
}
