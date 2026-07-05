<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimetableController extends Controller
{
    /**
     * Get all timetables with filters
     */
    public function index(Request $request)
    {
        try {
            $query = Timetable::with([
                'courseAllocation.course',
                'courseAllocation.lecturer',
                'lectureHall',
                'level',
                'academicSession'
            ])->scheduled();

            if ($request->has('academic_session_id')) {
                $query->where('academic_session_id', $request->academic_session_id);
            }

            if ($request->has('day')) {
                $query->where('day_of_week', $request->day);
            }

            if ($request->has('level_id')) {
                $query->where('level_id', $request->level_id);
            }

            if ($request->has('course_id')) {
                $query->whereHas('courseAllocation', function ($q) use ($request) {
                    $q->where('course_id', $request->course_id);
                });
            }

            $timetables = $query->orderBy('day_of_week')
                ->orderBy('start_time')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $timetables
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by day
     */
    public function byDay(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Timetable::with([
                'courseAllocation.course',
                'courseAllocation.lecturer',
                'lectureHall',
                'level'
            ])
                ->where('day_of_week', $request->day)
                ->scheduled();

            if ($request->has('academic_session_id')) {
                $query->where('academic_session_id', $request->academic_session_id);
            }

            $timetables = $query->orderBy('start_time')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $timetables
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming lectures
     */
    public function upcoming(Request $request)
    {
        try {
            $timetables = Timetable::with([
                'courseAllocation.course',
                'courseAllocation.lecturer',
                'lectureHall'
            ])
                ->scheduled()
                ->upcoming()
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $timetables
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable details
     */
    public function show($id)
    {
        try {
            $timetable = Timetable::with([
                'courseAllocation.course',
                'courseAllocation.lecturer.user',
                'lectureHall',
                'level',
                'attendance',
                'notifications'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $timetable
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable not found'
            ], 404);
        }
    }
}
