<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Mark attendance by QR code
     */
    public function markByQRCode($student, $timetableId)
    {
        try {
            $existingAttendance = Attendance::where('timetable_id', $timetableId)
                ->where('student_id', $student->id)
                ->first();

            if ($existingAttendance) {
                return [
                    'success' => false,
                    'message' => 'Attendance already marked'
                ];
            }

            $attendance = Attendance::create([
                'timetable_id' => $timetableId,
                'student_id' => $student->id,
                'check_in_time' => now(),
                'status' => 'present',
            ]);

            return [
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data' => $attendance
            ];
        } catch (\Exception $e) {
            Log::error('Mark attendance error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error marking attendance'
            ];
        }
    }

    /**
     * Get attendance statistics for student
     */
    public function getStudentStatistics($studentId)
    {
        try {
            $total = Attendance::where('student_id', $studentId)->count();
            $present = Attendance::where('student_id', $studentId)
                ->where('status', 'present')
                ->count();
            $absent = Attendance::where('student_id', $studentId)
                ->where('status', 'absent')
                ->count();
            $late = Attendance::where('student_id', $studentId)
                ->where('status', 'late')
                ->count();

            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'attendance_percentage' => $percentage
            ];
        } catch (\Exception $e) {
            Log::error('Get attendance statistics error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate attendance report
     */
    public function generateReport($filters = [])
    {
        try {
            $query = Attendance::with(['student.user', 'timetable.course']);

            if (isset($filters['student_id'])) {
                $query->where('student_id', $filters['student_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['from_date']) && isset($filters['to_date'])) {
                $query->whereBetween('created_at', [
                    $filters['from_date'],
                    $filters['to_date']
                ]);
            }

            return $query->paginate(50);
        } catch (\Exception $e) {
            Log::error('Generate attendance report error: ' . $e->getMessage());
            return null;
        }
    }
}
