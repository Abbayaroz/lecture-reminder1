<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\LecturerController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\TimetableOfficerController;
use App\Http\Controllers\Api\CourseController;

Route::prefix('api/v1')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/verify-email', [RegisterController::class, 'verifyEmail']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
        Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyResetToken']);
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    });

    // Protected Routes
    Route::middleware(['auth:api', 'user.status'])->group(function () {
        // Authentication
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [LoginController::class, 'logout']);
            Route::post('/refresh', [LoginController::class, 'refresh']);
            Route::post('/change-password', [PasswordResetController::class, 'changePassword']);
        });

        // Student Routes
        Route::middleware('role:student')->prefix('student')->group(function () {
            Route::get('/profile', [StudentController::class, 'profile']);
            Route::put('/profile', [StudentController::class, 'updateProfile']);
            Route::get('/courses', [StudentController::class, 'courses']);
            Route::get('/timetable', [StudentController::class, 'timetable']);
            Route::get('/attendance', [StudentController::class, 'attendance']);
            Route::get('/attendance-stats', [StudentController::class, 'attendanceStats']);
        });

        // Lecturer Routes
        Route::middleware('role:lecturer')->prefix('lecturer')->group(function () {
            Route::get('/profile', [LecturerController::class, 'profile']);
            Route::put('/profile', [LecturerController::class, 'updateProfile']);
            Route::get('/courses', [LecturerController::class, 'courses']);
            Route::get('/timetable', [LecturerController::class, 'timetable']);
            Route::get('/{id}', [LecturerController::class, 'show']);
        });

        // Admin Routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus']);
            Route::post('/lecturers/{id}/approve', [AdminController::class, 'approveLecturer']);
            Route::post('/lecturers/{id}/reject', [AdminController::class, 'rejectLecturer']);
            Route::get('/lecturers/pending', [AdminController::class, 'pendingLecturers']);
            Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword']);
        });

        // Timetable Officer Routes
        Route::middleware('role:timetable_officer')->prefix('timetable-officer')->group(function () {
            Route::post('/timetables', [TimetableOfficerController::class, 'createTimetable']);
            Route::put('/timetables/{id}', [TimetableOfficerController::class, 'updateTimetable']);
            Route::post('/timetables/{id}/cancel', [TimetableOfficerController::class, 'cancelTimetable']);
            Route::delete('/timetables/{id}', [TimetableOfficerController::class, 'deleteTimetable']);
            Route::post('/timetables/export', [TimetableOfficerController::class, 'exportTimetable']);
            Route::get('/timetables/conflicts', [TimetableOfficerController::class, 'getConflicts']);
        });

        // Timetable Routes (Public for authenticated users)
        Route::prefix('timetables')->group(function () {
            Route::get('/', [TimetableController::class, 'index']);
            Route::get('/by-day', [TimetableController::class, 'byDay']);
            Route::get('/upcoming', [TimetableController::class, 'upcoming']);
            Route::get('/{id}', [TimetableController::class, 'show']);
        });

        // Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'delete']);
            Route::delete('/', [NotificationController::class, 'deleteAll']);
        });

        // Attendance Routes
        Route::prefix('attendance')->group(function () {
            Route::post('/mark-qr', [AttendanceController::class, 'markByQRCode']);
            Route::get('/timetable/{id}', [AttendanceController::class, 'getTimetableAttendance']);
            Route::get('/report', [AttendanceController::class, 'report']);
        });

        // Course Routes
        Route::prefix('courses')->group(function () {
            Route::get('/', [CourseController::class, 'index']);
            Route::get('/{id}', [CourseController::class, 'show']);
            Route::get('/{id}/announcements', [CourseController::class, 'announcements']);
            Route::get('/{id}/lecture-notes', [CourseController::class, 'lectureNotes']);
            Route::get('/{id}/assignments', [CourseController::class, 'assignments']);
            Route::post('/{id}/announcements', [CourseController::class, 'createAnnouncement']);
        });
    });

    // Public Routes
    Route::prefix('lecturers')->group(function () {
        Route::get('/', [LecturerController::class, 'index']);
        Route::get('/{id}', [LecturerController::class, 'show']);
    });
});
