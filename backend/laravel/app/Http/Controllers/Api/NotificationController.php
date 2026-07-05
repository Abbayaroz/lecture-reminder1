<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $query = Notification::where('user_id', $user->id);

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('is_read')) {
                $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $notifications
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications
     */
    public function unread()
    {
        try {
            $user = auth()->user();
            $count = Notification::where('user_id', $user->id)
                ->unread()
                ->count();

            $notifications = Notification::where('user_id', $user->id)
                ->unread()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'count' => $count,
                'data' => $notifications
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();
            $notification = Notification::where('user_id', $user->id)
                ->findOrFail($id);

            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();
            Notification::where('user_id', $user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        try {
            $user = auth()->user();
            $notification = Notification::where('user_id', $user->id)
                ->findOrFail($id);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        try {
            $user = auth()->user();
            Notification::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
