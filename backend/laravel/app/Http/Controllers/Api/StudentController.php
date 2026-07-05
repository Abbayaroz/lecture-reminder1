<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Get student profile
     */
    public function profile()
    {
        try {
            $user = auth()->user();
            $student = Student::with(['user', 'programme', 'level'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }
    }

    /**
     * Update student profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $user->update($request->only([
                'date_of_birth', 'gender', 'address', 'city', 'state', 'country'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
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
     * Get student's courses
     */
    public function courses(Request $request)
    {
        try {
            $user = auth()->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $courses = StudentCourse::with(['courseAllocation.course', 'courseAllocation.lecturer'])
                ->where('student_id', $student->id)
                ->active()
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $courses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's timetable
     */
    public function timetable(Request $request)
    {
        try {
            $user = auth()->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $query = StudentCourse::with([
                'courseAllocation.course',
                'courseAllocation.timetables.lectureHall',
                'courseAllocation.timetables.lecturer'
            ])
                ->where('student_id', $student->id)
                ->active();

            if ($request->has('day')) {
                $query->whereHas('courseAllocation.timetables', function ($q) use ($request) {
                    $q->where('day_of_week', $request->day);
                });
            }

            $courses = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $courses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's attendance records
     */
    public function attendance(Request $request)
    {
        try {
            $user = auth()->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $query = Attendance::with(['timetable.course', 'timetable.courseAllocation.course'])
                ->where('student_id', $student->id);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $attendance = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $attendance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function attendanceStats()
    {
        try {
            $user = auth()->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $total = Attendance::where('student_id', $student->id)->count();
            $present = Attendance::where('student_id', $student->id)
                ->where('status', 'present')
                ->count();
            $absent = Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->count();
            $late = Attendance::where('student_id', $student->id)
                ->where('status', 'late')
                ->count();

            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'percentage' => $percentage
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
