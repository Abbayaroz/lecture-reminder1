<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class TimetableReminderService
{
    /**
     * Send lecture reminders
     */
    public function sendLectureReminders()
    {
        try {
            // Get timetables for next 30 minutes
            $timetables = Timetable::where('status', 'scheduled')
                ->whereDate('created_at', now()->date())
                ->get();

            foreach ($timetables as $timetable) {
                $this->sendReminderForTimetable($timetable);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Reminder service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder for a specific timetable
     */
    private function sendReminderForTimetable(Timetable $timetable)
    {
        try {
            $courseAllocation = $timetable->courseAllocation;
            $studentCourses = $courseAllocation->studentCourses()->active()->get();

            $notificationService = new NotificationService();

            foreach ($studentCourses as $studentCourse) {
                $user = $studentCourse->student->user;
                $course = $courseAllocation->course;

                $title = 'Lecture Reminder';
                $message = "Your lecture for {$course->title} is starting soon at {$timetable->lectureHall->name}";

                // Create in-app notification
                $notificationService->createNotification(
                    $user,
                    'lecture_reminder',
                    $title,
                    $message,
                    [
                        'timetable_id' => $timetable->id,
                        'course_id' => $course->id,
                        'venue' => $timetable->lectureHall->name,
                        'time' => $timetable->start_time,
                    ],
                    $timetable->id
                );

                // Send push notification
                $notificationService->sendPushNotification($user, $title, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error sending timetable reminder: ' . $e->getMessage());
        }
    }
}
