<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\AssignTask;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard - FIXED FOR INERTIA
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Access denied to Admin Dashboard');
        }

        $activeTab = $request->tab ?? 'profile';

        // Fetch stats here and pass to Inertia
        $stats = [
            'totalUsers'      => User::count(),
            'totalEmployees'  => User::role('employee')->count(),
            'totalTasks'      => AssignTask::count(),
            'pendingTasks'    => AssignTask::where('status', 'pending')->count(),
            'completedTasks'  => AssignTask::where('status', 'completed')->count(),
            'financeReports'  => 0, // Replace with your logic
            'systemSettings'  => true,
        ];

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'roles' => ['admin'],
            'menuItems' => [],
            'activeTab' => $activeTab,
            'stats' => $stats, // Pass stats directly
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'recent_activities' => [
                'Employee attendance updated',
                'New project assigned',
                'Monthly report generated',
                'Task deadlines approaching',
            ],
            'notifications' => [],
        ]);
    }

    /**
     * Get admin dashboard statistics - FOR API REFRESH ONLY
     */
    public function getAdminStats()
    {
        try {
            $stats = [
                'totalUsers'      => User::count(),
                'totalEmployees'  => User::role('employee')->count(),
                'totalTasks'      => AssignTask::count(),
                'pendingTasks'    => AssignTask::where('status', 'pending')->count(),
                'completedTasks'  => AssignTask::where('status', 'completed')->count(),
                'financeReports'  => 0, // Replace with your logic
                'systemSettings'  => true,
            ];

            return response()->json([
                'success' => true,
                'data'    => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to fetch admin statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch employees in the same department as the admin
     */
    public function getEmployees()
    {
        $admin = Auth::user();

        $employees = User::where('department', $admin->department)
            ->where('role', 'employee')
            ->get(['id', 'employee_id', 'name']);

        return response()->json($employees);
    }
    
    // Show profile edit page
    public function editProfile()
    {
        $user = Auth::user();
        return Inertia::render('Admin/Profile', [
            'user' => $user,
        ]);
    }

    // Update profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'role' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'department' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (isset($data['username'])) {
            $user->username = $data['username'];
        }
        
        if (isset($data['gender'])) {
            $user->gender = $data['gender'];
        }
        
        if (isset($data['department'])) {
            $user->department = $data['department'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Store a new assigned task
     */
  
    public function storeTask(Request $request)
    {
        $admin = Auth::user();

        $validated = $request->validate([
            'employee_id'      => 'required|exists:users,id',
            'work_location'    => 'required|string|max:50',
            'project_name'     => 'required|string|max:100',
            'task_title'       => 'required|string|max:255',
            'task_type'        => 'required|string|max:100',
            'task_priority'    => 'required|string|max:50',
            'deadline'         => 'required|date',
            'task_description' => 'required|string',
            'attachment'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png',
        ]);

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tasks', 'public');
        }

        // Create the task
        $task = AssignTask::create([
            'admin_id'        => $admin->id,
            'employee_id'     => $validated['employee_id'],
            'task_title'      => $validated['task_title'],
            'task_type'       => $validated['task_type'],
            'task_priority'   => $validated['task_priority'],
            'deadline'        => $validated['deadline'],
            'task_description'=> $validated['task_description'],
            'work_location'   => $validated['work_location'],
            'project_name'    => $validated['project_name'],
            'attachment'      => $attachmentPath,
        ]);

        // Build URL for frontend
        $task->attachment_url = $attachmentPath ? Storage::url($attachmentPath) : null;
        

        return response()->json([
            'message' => 'Task assigned successfully.',
            'task'    => $task,
        ]);
    }

    /**
     * Show employee management.
     */
    public function employeeManagement()
    {
        $employees = User::role('employee')->paginate(15);

        return Inertia::render('Admin/EmployeeManagement', [
            'employees' => $employees,
        ]);
    }

    /**
     * Show project management.
     */
    public function projectManagement()
    {
        return Inertia::render('Admin/ProjectManagement', [
            'projects' => [],
            'statuses' => ['active', 'pending', 'completed', 'on-hold'],
        ]);
    }

    /**
     * Show reports dashboard.
     */
    public function reports()
    {
        return Inertia::render('Admin/Reports', [
            'monthly_stats' => [],
            'charts_data'   => [],
        ]);
    }
    
   
   
    /**
     * Get work reports from assign_tasks
     */
    public function getWorkReports(Request $request)
    {
        try {
            $adminId = Auth::id();

            $reports = DB::table('assign_tasks')
                ->join('users', 'assign_tasks.employee_id', '=', 'users.id')
                ->where('assign_tasks.admin_id', $adminId)
                ->select(
                    'assign_tasks.*',
                    'users.name as employee_name',
                    'users.email as employee_email'
                )
                ->orderByDesc('assign_tasks.updated_at')
                ->get()
                ->map(function ($report) {
                    $created = new Carbon($report->created_at);
                    $updated = new Carbon($report->updated_at);
                    return array_merge((array) $report, [
                        'hours_worked'     => $created->diffInHours($updated),
                        'date'             => $report->updated_at,
                        'work_description' => $report->task_description,
                    ]);
                });

            return response()->json(['success' => true, 'reports' => $reports]);
        } catch (\Exception $e) {
            \Log::error('Work Reports Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch work reports',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update work report status with proper review tracking
     */
    public function updateWorkReport(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status'   => 'required|in:approved,rejected,completed,pending',
                'comments' => 'nullable|string'
            ]);

            $adminId = Auth::id();

            // Find the task
            $task = AssignTask::find($id);

            if (!$task) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Task not found'
                ], 404);
            }

            // Check if the admin owns this task
            if ($task->admin_id !== $adminId) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Prepare update data
            $updateData = [
                'status' => $validated['status'],
                'reviewed_by_admin_id' => $adminId,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ];

            // Add comments based on status
            if ($validated['status'] === 'approved') {
                $updateData['goals_met'] = $validated['comments'] ?? 'Task approved';
            } elseif ($validated['status'] === 'rejected') {
                $updateData['issues_encountered'] = $validated['comments'] ?? 'Task rejected';
                $updateData['unfinished_task'] = 'See issues encountered';
            }

            // Update the task
            $task->update($updateData);

            // Fetch updated task with employee info
            $updatedTask = DB::table('assign_tasks')
                ->join('users', 'assign_tasks.employee_id', '=', 'users.id')
                ->where('assign_tasks.id', $id)
                ->select(
                    'assign_tasks.*',
                    'users.name as employee_name',
                    'users.email as employee_email'
                )
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
                'task' => $updatedTask,
                'reviewed_by_admin_id' => $adminId,
                'reviewed_at' => now()->toDateTimeString()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update Work Report Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative method if you prefer using DB facade instead of Eloquent
     */
    public function updateWorkReportDB(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status'   => 'required|in:approved,rejected,completed,pending',
                'comments' => 'nullable|string'
            ]);

            $adminId = Auth::id();

            // Check if task exists and belongs to admin
            $task = DB::table('assign_tasks')
                ->where('id', $id)
                ->where('admin_id', $adminId)
                ->first();

            if (!$task) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Task not found or unauthorized'
                ], 404);
            }

            // Prepare update data
            $updateData = [
                'status' => $validated['status'],
                'reviewed_by_admin_id' => $adminId,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ];

            // Add comments based on status
            if ($validated['status'] === 'approved') {
                $updateData['goals_met'] = $validated['comments'] ?? 'Task approved';
            } elseif ($validated['status'] === 'rejected') {
                $updateData['issues_encountered'] = $validated['comments'] ?? 'Task rejected';
                $updateData['unfinished_task'] = 'See issues encountered';
            }

            // Update the task
            DB::table('assign_tasks')
                ->where('id', $id)
                ->update($updateData);

            // Fetch updated task with employee info
            $updatedTask = DB::table('assign_tasks')
                ->join('users', 'assign_tasks.employee_id', '=', 'users.id')
                ->where('assign_tasks.id', $id)
                ->select(
                    'assign_tasks.*',
                    'users.name as employee_name',
                    'users.email as employee_email'
                )
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
                'task' => $updatedTask,
                'reviewed_by_admin_id' => $adminId,
                'reviewed_at' => now()->toDateTimeString()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update Work Report Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getEmployeeTasks(Request $request)
    {
        try {
            $adminId = auth()->id();
            $employeeId = session('employee_id');
            
            $tasks = DB::table('supervisor_tasks')
                ->where('supervisor_id', $adminId)
                ->where('employee_id', $employeeId)
                ->where('status', 'Pending')
                ->orderBy('deadline', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks'
            ], 500);
        }
    }
}
