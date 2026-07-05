<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send push notification
     */
    public function sendPushNotification(User $user, $title, $message, $data = [])
    {
        try {
            $deviceTokens = DeviceToken::where('user_id', $user->id)
                ->active()
                ->get();

            foreach ($deviceTokens as $token) {
                // Firebase Cloud Messaging integration
                // This would use Firebase Admin SDK
                $this->sendToFirebase($token->device_token, $title, $message, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Push notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     */
    public function sendEmailNotification(User $user, $title, $message)
    {
        try {
            // Send via Laravel Mail
            // Mail::send('emails.notification', [
            //     'title' => $title,
            //     'message' => $message,
            // ], function ($message) use ($user) {
            //     $message->to($user->email)->subject($title);
            // });

            return true;
        } catch (\Exception $e) {
            Log::error('Email notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create in-app notification
     */
    public function createNotification(User $user, $type, $title, $message, $data = [], $timetableId = null)
    {
        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'timetable_id' => $timetableId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'notification_channel' => 'in_app',
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Create notification error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send to Firebase (placeholder)
     */
    private function sendToFirebase($deviceToken, $title, $message, $data)
    {
        // Firebase Admin SDK implementation
        // This is a placeholder - implement according to your Firebase setup
    }
}
