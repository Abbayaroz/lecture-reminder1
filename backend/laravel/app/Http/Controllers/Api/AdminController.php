<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\TimetableOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_students' => Student::count(),
                'total_lecturers' => Lecturer::count(),
                'total_admins' => User::byRole('admin')->count(),
                'active_users' => User::active()->count(),
                'pending_approvals' => Lecturer::where('is_approved', false)->count() + 
                                      TimetableOfficer::where('is_approved', false)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users
     */
    public function users(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $users = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, $userId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,suspended,pending_approval',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($userId);
            $user->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve lecturer
     */
    public function approveLecturer($lecturerId)
    {
        try {
            $lecturer = Lecturer::findOrFail($lecturerId);
            $lecturer->update([
                'is_approved' => true,
                'approval_date' => now(),
                'approved_by' => auth()->id(),
            ]);

            $lecturer->user->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'Lecturer approved',
                'data' => $lecturer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject lecturer
     */
    public function rejectLecturer(Request $request, $lecturerId)
    {
        try {
            $lecturer = Lecturer::findOrFail($lecturerId);
            $lecturer->delete();
            $lecturer->user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lecturer rejected'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending lecturers
     */
    public function pendingLecturers()
    {
        try {
            $lecturers = Lecturer::with(['user', 'department'])
                ->where('is_approved', false)
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $lecturers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(Request $request, $userId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($userId);
            $user->update(['password' => Hash::make($request->password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successful'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
