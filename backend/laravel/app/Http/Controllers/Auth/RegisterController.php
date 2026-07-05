<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RegisterController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|string|email|unique:users,email',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:student,lecturer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->role === 'student' ? 'active' : 'pending_approval',
            ]);

            // If student, create student record
            if ($request->role === 'student' && $request->has('registration_number')) {
                Student::create([
                    'user_id' => $user->id,
                    'registration_number' => $request->registration_number,
                    'programme_id' => $request->programme_id,
                    'level_id' => $request->level_id,
                    'admission_date' => now()->date(),
                ]);
            }

            // Send email verification
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please verify your email.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'hash' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($request->user_id);

            if (hash_equals((string) $request->hash, sha1($user->email))) {
                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
