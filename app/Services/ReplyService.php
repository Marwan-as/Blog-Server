<?php

namespace App\Services;

use App\Models\Reply;
use Illuminate\Support\Facades\DB;
use App\Notifications\ReplyNotification;
use App\Http\Requests\Reply\StoreReplyRequest;
use Exception;
use InvalidArgumentException;

class ReplyService
{
    /**
     * Create a new reply and notify the comment owner if applicable.
     *
     * @param StoreReplyRequest $request The validated request containing reply data.
     * @return Reply The created reply instance.
     * @throws Exception If the reply creation fails.
     */
    public function createReply(StoreReplyRequest $request): Reply
    {
        $data = $request->validated();
        $user = $request->user();
        $data['user_id'] = $user->id;

        $reply = DB::transaction(fn() => Reply::create($data));

        // Notify the comment owner if the reply was not created by them
        $commentOwner = $reply->comment->user;
        if ($commentOwner->id !== $user->id) {
            $commentOwner->notify(new ReplyNotification($reply, $user));
        }

        return $reply;
    }

    /**
     * Delete a reply.
     *
     * @param Reply $reply The reply instance to delete.
     * @return bool Whether the deletion was successful.
     */
    public function deleteReply(Reply $reply): bool
    {
        return $reply->delete();
    }
}
