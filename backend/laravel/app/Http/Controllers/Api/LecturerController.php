<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LecturerController extends Controller
{
    /**
     * Get all lecturers
     */
    public function index(Request $request)
    {
        try {
            $query = Lecturer::with(['user', 'department', 'courses'])->approved();

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $lecturers = $query->paginate($request->get('per_page', 15));

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
     * Get lecturer profile
     */
    public function show($id)
    {
        try {
            $lecturer = Lecturer::with(['user', 'department', 'courses.course', 'courses.timetables'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $lecturer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lecturer not found'
            ], 404);
        }
    }

    /**
     * Get current lecturer profile
     */
    public function profile()
    {
        try {
            $user = auth()->user();
            $lecturer = Lecturer::with(['user', 'department', 'courses'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $lecturer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lecturer profile not found'
            ], 404);
        }
    }

    /**
     * Update lecturer profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:50',
                'specialization' => 'nullable|string',
                'qualifications' => 'nullable|string',
                'office_location' => 'nullable|string|max:255',
                'office_phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $lecturer = Lecturer::where('user_id', $user->id)->firstOrFail();

            $lecturer->update($request->only([
                'title', 'specialization', 'qualifications', 'office_location', 'office_phone'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
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
     * Get lecturer's courses
     */
    public function courses(Request $request)
    {
        try {
            $user = auth()->user();
            $courses = Lecturer::where('user_id', $user->id)
                ->firstOrFail()
                ->courses()
                ->with(['course', 'academicSession', 'timetables'])
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
     * Get lecturer's timetable
     */
    public function timetable(Request $request)
    {
        try {
            $user = auth()->user();
            $lecturer = Lecturer::where('user_id', $user->id)->firstOrFail();

            $query = $lecturer->courses()
                ->with(['course', 'timetables.lectureHall', 'timetables.level'])
                ->active();

            if ($request->has('day')) {
                $query->whereHas('timetables', function ($q) use ($request) {
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
}
