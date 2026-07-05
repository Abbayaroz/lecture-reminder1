<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAllocation;
use App\Models\Announcement;
use App\Models\LectureNote;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Get all courses
     */
    public function index(Request $request)
    {
        try {
            $query = Course::with('department')->active();

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%')
                        ->orWhere('title', 'like', '%' . $request->search . '%');
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
     * Get course details
     */
    public function show($id)
    {
        try {
            $course = Course::with(['department', 'allocations.lecturer', 'announcements'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }
    }

    /**
     * Get course announcements
     */
    public function announcements($courseId)
    {
        try {
            $announcements = Announcement::with(['lecturer', 'course'])
                ->where('course_id', $courseId)
                ->published()
                ->orderBy('publish_date', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course lecture notes
     */
    public function lectureNotes($courseId)
    {
        try {
            $query = CourseAllocation::where('course_id', $courseId)
                ->active()
                ->with('lectureNotes');

            $notes = $query->get()->flatMap(function ($allocation) {
                return $allocation->lectureNotes()->published()->get();
            });

            return response()->json([
                'success' => true,
                'data' => $notes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course assignments
     */
    public function assignments($courseId)
    {
        try {
            $assignments = Assignment::whereHas('courseAllocation', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
                ->published()
                ->orderBy('due_date', 'asc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $assignments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create announcement
     */
    public function createAnnouncement(Request $request, $courseId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'priority' => 'in:low,normal,high,urgent',
                'expiry_date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $lecturer = $user->lecturer;

            $announcement = Announcement::create([
                'course_id' => $courseId,
                'lecturer_id' => $lecturer->id,
                'title' => $request->title,
                'content' => $request->content,
                'priority' => $request->get('priority', 'normal'),
                'expiry_date' => $request->expiry_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => $announcement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
