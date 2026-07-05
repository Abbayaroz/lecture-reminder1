<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\CourseAllocation;
use App\Models\LectureHall;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TimetableOfficerController extends Controller
{
    /**
     * Create timetable
     */
    public function createTimetable(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'academic_session_id' => 'required|exists:academic_sessions,id',
                'course_allocation_id' => 'required|exists:course_allocation,id',
                'lecture_hall_id' => 'required|exists:lecture_halls,id',
                'level_id' => 'required|exists:levels,id',
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'semester' => 'required|in:first,second',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for conflicts
            $conflict = Timetable::where('lecture_hall_id', $request->lecture_hall_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('academic_session_id', $request->academic_session_id)
                ->where('status', 'scheduled')
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                        ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot conflict detected. Please choose another time.'
                ], 400);
            }

            $timetable = Timetable::create($request->only([
                'academic_session_id',
                'course_allocation_id',
                'lecture_hall_id',
                'level_id',
                'day_of_week',
                'start_time',
                'end_time',
                'semester',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Timetable created successfully',
                'data' => $timetable
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update timetable
     */
    public function updateTimetable(Request $request, $timetableId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lecture_hall_id' => 'sometimes|exists:lecture_halls,id',
                'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $timetable = Timetable::findOrFail($timetableId);

            // Check for conflicts if changing time/hall
            if ($request->has('lecture_hall_id') || $request->has('day_of_week') || 
                $request->has('start_time') || $request->has('end_time')) {
                $hallId = $request->get('lecture_hall_id', $timetable->lecture_hall_id);
                $day = $request->get('day_of_week', $timetable->day_of_week);
                $startTime = $request->get('start_time', $timetable->start_time);
                $endTime = $request->get('end_time', $timetable->end_time);

                $conflict = Timetable::where('id', '!=', $timetableId)
                    ->where('lecture_hall_id', $hallId)
                    ->where('day_of_week', $day)
                    ->where('academic_session_id', $timetable->academic_session_id)
                    ->where('status', 'scheduled')
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime]);
                    })
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time slot conflict detected.'
                    ], 400);
                }
            }

            $timetable->update($request->only([
                'lecture_hall_id', 'day_of_week', 'start_time', 'end_time'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Timetable updated successfully',
                'data' => $timetable
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel timetable
     */
    public function cancelTimetable(Request $request, $timetableId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $timetable = Timetable::findOrFail($timetableId);
            $timetable->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timetable cancelled',
                'data' => $timetable
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete timetable
     */
    public function deleteTimetable($timetableId)
    {
        try {
            $timetable = Timetable::findOrFail($timetableId);
            $timetable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timetable deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export timetable to PDF/Excel
     */
    public function exportTimetable(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'format' => 'required|in:pdf,excel',
                'academic_session_id' => 'required|exists:academic_sessions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $timetables = Timetable::with([
                'courseAllocation.course',
                'courseAllocation.lecturer',
                'lectureHall',
                'level'
            ])
                ->where('academic_session_id', $request->academic_session_id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            // For PDF/Excel export, you would use libraries like Laravel Excel or DOMPDF
            // This is a placeholder response
            return response()->json([
                'success' => true,
                'message' => 'Timetable exported successfully',
                'data' => $timetables,
                'format' => $request->format
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lecture conflicts
     */
    public function getConflicts(Request $request)
    {
        try {
            $conflicts = [];
            $timetables = Timetable::where('academic_session_id', $request->academic_session_id)
                ->where('status', 'scheduled')
                ->get();

            foreach ($timetables as $timetable) {
                $conflicting = Timetable::where('id', '!=', $timetable->id)
                    ->where('lecture_hall_id', $timetable->lecture_hall_id)
                    ->where('day_of_week', $timetable->day_of_week)
                    ->where('academic_session_id', $timetable->academic_session_id)
                    ->where('status', 'scheduled')
                    ->where(function ($query) use ($timetable) {
                        $query->whereBetween('start_time', [$timetable->start_time, $timetable->end_time])
                            ->orWhereBetween('end_time', [$timetable->start_time, $timetable->end_time]);
                    })
                    ->get();

                if ($conflicting->count() > 0) {
                    $conflicts[] = [
                        'timetable' => $timetable,
                        'conflicts' => $conflicting
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'count' => count($conflicts),
                'data' => $conflicts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
