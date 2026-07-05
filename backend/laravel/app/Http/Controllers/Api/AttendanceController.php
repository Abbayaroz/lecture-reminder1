<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Timetable;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Mark attendance with QR code
     */
    public function markByQRCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'qr_code_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            // Find timetable by QR code token (you would typically decode/verify the QR code)
            $timetable = Timetable::where('id', $request->qr_code_token)
                ->scheduled()
                ->firstOrFail();

            // Check if attendance already marked
            $existingAttendance = Attendance::where('timetable_id', $timetable->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already marked for this lecture'
                ], 400);
            }

            // Mark attendance
            $attendance = Attendance::create([
                'timetable_id' => $timetable->id,
                'student_id' => $student->id,
                'check_in_time' => now(),
                'status' => 'present',
                'qr_code_token' => $request->qr_code_token,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data' => $attendance
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance for a timetable
     */
    public function getTimetableAttendance($timetableId)
    {
        try {
            $attendance = Attendance::with(['student.user', 'markedBy'])
                ->where('timetable_id', $timetableId)
                ->paginate(50);

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
     * Generate attendance report
     */
    public function report(Request $request)
    {
        try {
            $query = Attendance::with(['student.user', 'timetable.course']);

            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date,
                    $request->to_date
                ]);
            }

            $attendance = $query->paginate($request->get('per_page', 50));

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
}
