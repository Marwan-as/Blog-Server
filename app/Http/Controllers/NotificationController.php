<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function getUnreadNotifications(Request $request)
    {
        $user = $request->user();
        return response()->json(['notifications' => $user->unreadNotifications], 200);
    }


    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read'], 200);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }


    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read'], 200);
    }


    public function getAllNotifications(Request $request)
    {
        $user = $request->user();
        return response()->json(['notifications' => $user->notifications], 200);
    }
}
