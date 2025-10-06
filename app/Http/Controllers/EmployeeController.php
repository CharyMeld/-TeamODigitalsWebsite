<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Show the employee dashboard with attendance data.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $userId = $user->id;
        $today = Carbon::today()->toDateString();

        // Get today's attendance
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // Get recent attendance history
        $history = Attendance::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Calculate recent activities based on attendance
        $recent_activities = $this->getRecentActivities($attendance, $history);

        return Inertia::render('Employee/Dashboard', [
            'user' => $user,
            'attendance' => $attendance,
            'history' => $history,
            'role' => 'employee',
            'recent_activities' => $recent_activities,
            'upcoming_deadlines' => [
                // Add deadline logic here
            ],
            'notifications' => [
                // Add employee notifications here
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Generate recent activities from attendance + history.
     */
    private function getRecentActivities($attendance, $history)
    {
        $activities = [];

        // Today's record
        if ($attendance) {
            if ($attendance->sign_in_time) {
                $activities[] = [
                    'action' => 'Signed In',
                    'time' => $attendance->sign_in_time,
                    'date' => $attendance->date,
                ];
            }
            if ($attendance->break_time_out) {
                $activities[] = [
                    'action' => 'Started Break',
                    'time' => $attendance->break_time_out,
                    'date' => $attendance->date,
                ];
            }
            if ($attendance->break_time_in) {
                $activities[] = [
                    'action' => 'Ended Break',
                    'time' => $attendance->break_time_in,
                    'date' => $attendance->date,
                ];
            }
            if ($attendance->sign_out_time) {
                $activities[] = [
                    'action' => 'Signed Out',
                    'time' => $attendance->sign_out_time,
                    'date' => $attendance->date,
                ];
            }
        }

        // Past history
        foreach ($history as $record) {
            $activities[] = [
                'action' => 'Worked ' . round($record->hours_worked ?? 0, 2) . ' hrs',
                'time'   => $record->sign_out_time ?? $record->sign_in_time,
                'date'   => $record->date,
            ];
        }

        // Sort by most recent first
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) <=> strtotime($a['time']);
        });

        return $activities;
    }

    /**
     * Show employee tasks.
     */
    public function tasks()
    {
        return Inertia::render('Employee/Tasks', [
            'tasks' => [], // Add your tasks data here
            'filters' => ['all', 'pending', 'in_progress', 'completed'],
        ]);
    }

    /**
     * API: Get tasks assigned to the current employee
     */
    public function getMyTasks(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get tasks from assign_tasks table where employee_id matches current user
            // Only show tasks that are pending or in_progress (not completed, rejected, approved, or submitted)
            $tasks = DB::table('assign_tasks')
                ->where('employee_id', $user->id)
                ->whereNotIn('status', ['completed', 'Submitted', 'approved', 'rejected'])
                ->orderBy('deadline', 'asc')
                ->orderBy('task_priority', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch employee tasks: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load tasks',
                'tasks' => []
            ], 500);
        }
    }

    /**
     * Show time tracking.
     */
    public function timeTracking()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Calculate time tracking stats
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $monthStart = $today->copy()->startOfMonth();

        $todayHours = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->sum('hours_worked') ?? 0;

        $weekHours = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$weekStart, $today])
            ->sum('hours_worked') ?? 0;

        $monthHours = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$monthStart, $today])
            ->sum('hours_worked') ?? 0;

        $timeEntries = Attendance::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return Inertia::render('Employee/TimeTracking', [
            'today_hours' => round($todayHours, 2), 
            'week_hours' => round($weekHours, 2),
            'month_hours' => round($monthHours, 2),
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Show employee reports
     */
    public function reports()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get monthly attendance data
        $monthlyReports = Attendance::where('user_id', $userId)
            ->selectRaw('
                YEAR(date) as year,
                MONTH(date) as month,
                COUNT(*) as days_worked,
                SUM(hours_worked) as total_hours,
                AVG(hours_worked) as avg_hours
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return Inertia::render('Employee/Reports', [
            'monthly_reports' => $monthlyReports,
            'performance_metrics' => [
                'attendance_rate' => '95%', // Calculate actual rate
                'avg_hours_per_day' => round($monthlyReports->first()->avg_hours ?? 0, 2),
                'total_days_worked' => $monthlyReports->sum('days_worked'),
            ],
        ]);
    }

    /**
     * Show employee schedule
     */
    public function schedule()
    {
        return Inertia::render('Employee/Schedule', [
            'current_schedule' => [],
            'upcoming_shifts' => [],
        ]);
    }

    /**
     * API: Get all employees (for admin use)
     */
    public function index(): JsonResponse
    {
        try {
            $employees = User::select('id', 'name', 'email', 'department', 'role', 'created_at')
                ->where('role', 'employee')
                ->orderBy('name')
                ->get();

            return response()->json([
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch employees',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Show specific employee
     */
    public function show($id): JsonResponse
    {
        try {
            $employee = User::findOrFail($id);
            
            return response()->json([
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Employee not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show employee leave requests page
     */
    public function leaveRequests()
    {
        $user = Auth::user();

        // Fetch all leave requests for this employee
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Employee/LeaveTab', [
            'user' => $user,
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Submit a new leave request
     */
    public function submitLeaveRequest(Request $request)
    {
        $request->validate([
            'leave_type'   => 'required|string',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'reason'       => 'required|string|max:500',
        ]);

        $user = Auth::user();

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave_attachments', 'public');
        }

        LeaveRequest::create([
            'user_id'                 => $user->id,
            'employee_name'           => $user->name,
            'employee_id'             => $user->employee_id ?? null,
            'department'              => $user->department ?? null,
            'job_title'               => $user->role ?? null,
            'contact'                 => $user->address ?? null,
            'leave_type'              => $request->leave_type,
            'start_date'              => $request->start_date,
            'end_date'                => $request->end_date,
            'number_of_days'          => $request->number_of_days,
            'reason'                  => $request->reason,
            'superadmin'              => null,
            'status'                  => 'Pending',
            'comments'                => null,
            'attachment'              => $path,
            'employee_acknowledgement'=> $request->employee_acknowledgement ? 1 : 0,
        ]);

        return redirect()
            ->route('employee.leaveRequests')
            ->with('success', 'Leave request submitted successfully.');
    }
        
    /**
     * Complete a task with report submission
     */
    public function completeTask(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|numeric',
                'files_worked_on' => 'required|string',
                'goals_met' => 'required|string',
                'report_file' => 'nullable|file|max:10240', // 10MB max
            ]);

            $user = Auth::user();
            $taskId = $request->input('id');
            
            // Verify task belongs to employee and is not already completed/submitted
            $task = DB::table('assign_tasks')
                ->where('id', $taskId)
                ->where('employee_id', $user->id)
                ->whereNotIn('status', ['completed', 'Submitted', 'approved', 'rejected'])
                ->first();

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found or already submitted.'
                ], 404);
            }

            // Handle file upload
            $reportFile = null;
            if ($request->hasFile('report_file')) {
                $uploadDir = 'uploads/reports/';
                if (!is_dir(public_path($uploadDir))) {
                    mkdir(public_path($uploadDir), 0755, true);
                }
                
                $file = $request->file('report_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path($uploadDir), $filename);
                $reportFile = $uploadDir . $filename;
            }

            // Update task with report data
            DB::table('assign_tasks')
                ->where('id', $taskId)
                ->where('employee_id', $user->id)
                ->update([
                    'files_worked_on' => $request->input('files_worked_on'),
                    'file_names_or_ids' => $request->input('file_names_or_ids'),
                    'goals_met' => $request->input('goals_met'),
                    'collaborators' => $request->input('collaborators'),
                    'issues_encountered' => $request->input('issues_encountered', ''),
                    'unfinished_task' => $request->input('unfinished_task'),
                    'attachment' => $reportFile,
                    'status' => 'Submitted',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Task report submitted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Task completion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit task report. Please try again.'
            ], 500);
        }
    }
}
