<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * Send password reset email
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            // Invalidate previous tokens
            PasswordResetToken::where('user_id', $user->id)
                ->where('is_used', false)
                ->delete();

            // Generate reset token
            $token = Str::random(64);
            $resetToken = PasswordResetToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]);

            // Send reset email
            // Mail::send('emails.password-reset', [
            //     'user' => $user,
            //     'token' => $token,
            //     'resetUrl' => config('app.frontend_url') . '/reset-password?token=' . $token,
            // ], function ($message) use ($user) {
            //     $message->to($user->email)->subject('Password Reset Request');
            // });

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $resetToken = PasswordResetToken::where('token', $request->token)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Token is valid',
                'user_id' => $resetToken->user_id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $resetToken = PasswordResetToken::where('token', $request->token)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            $user = $resetToken->user;
            $user->update(['password' => Hash::make($request->password)]);

            $resetToken->update([
                'is_used' => true,
                'used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successful'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password (authenticated)
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed|different:current_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->update(['password' => Hash::make($request->new_password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password change failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
