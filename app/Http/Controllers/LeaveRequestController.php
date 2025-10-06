<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Get all leave requests (Admin view)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = DB::table('leave_requests');

        if ($user->role === 'admin') {
            $query->whereNotIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('role', 'admin');
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $leaveRequests
        ]);
    }

    /**
     * Submit new leave request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|string|in:Annual Leave,Sick Leave,Emergency Leave,Maternity Leave,Paternity Leave,Study Leave,Compassionate Leave,Medical Leave',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);
        $numberOfDays = $startDate->diffInDays($endDate) + 1;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $attachmentPath = $file->storeAs('leave_attachments', $fileName, 'public');
        }

        $leaveRequestId = DB::table('leave_requests')->insertGetId([
            'user_id' => $user->id,
            'employee_name' => $user->name,
            'employee_id' => $user->employee_id ?? 'EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'department' => $user->department ?? 'Not Set',
            'job_title' => $user->job_title ?? 'Not Set',
            'contact' => $user->email,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'number_of_days' => $numberOfDays,
            'reason' => $request->reason,
            'superadmin' => $user->role === 'admin' ? 'Pending' : 'Approved',
            'status' => 'Pending',
            'comments' => '',
            'attachment' => $attachmentPath,
            'employee_acknowledgement' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => [
                'id' => $leaveRequestId,
                'status' => 'pending',
                'approval_level' => $user->role === 'admin' ? 'superadmin' : 'admin'
            ]
        ]);
    }

    /**
     * Approve / Decline leave request
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status'   => 'required|string|in:Approved,Declined',
            'comments' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $leaveRequest = DB::table('leave_requests')->where('id', $id)->first();
        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found'], 404);
        }

        $requestUser = DB::table('users')->where('id', $leaveRequest->user_id)->first();
        if ($user->role === 'admin' && $requestUser->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Only superadmin can approve/decline admin requests'], 403);
        }

        $updateData = [
            'status' => $request->status,
            'comments' => $request->comments ?? '',
            'updated_at' => now()
        ];

        if ($user->role === 'superadmin' && $requestUser->role === 'admin') {
            $updateData['superadmin'] = $request->status;
        }

        DB::table('leave_requests')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Leave request {$request->status} successfully",
            'data' => $updateData
        ]);
    }

    /**
     * Cancel a leave request
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $leaveRequest = DB::table('leave_requests')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found or access denied'], 404);
        }

        if ($leaveRequest->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be cancelled'], 400);
        }

        DB::table('leave_requests')->where('id', $id)->delete();

        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        return response()->json(['success' => true, 'message' => 'Leave request cancelled successfully']);
    }

    /**
     * Get current user's leave requests
     */
    public function myRequests()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $leaveRequests = DB::table('leave_requests')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $leaveRequests]);
    }

    /**
     * Get a single leave request
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $leaveRequest = DB::table('leave_requests')->where('id', $id)->first();

        if (!$leaveRequest) {
            return response()->json(['success' => false, 'message' => 'Leave request not found'], 404);
        }

        // Employees can only view their own requests
        if ($user->role === 'employee' && $leaveRequest->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        return response()->json(['success' => true, 'data' => $leaveRequest]);
    }

    /**
     * Get statistics for admin/superadmin
     */
    public function stats()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $baseQuery = DB::table('leave_requests');

        if ($user->role === 'admin') {
            $baseQuery->whereNotIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('role', 'admin');
            });
        }

        $stats = [
            'total'    => $baseQuery->count(),
            'pending'  => (clone $baseQuery)->where('status', 'Pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'Approved')->count(),
            'declined' => (clone $baseQuery)->where('status', 'Declined')->count(),
            'this_month' => (clone $baseQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count()
        ];

        $recentRequests = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'employee_name', 'leave_type', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_requests' => $recentRequests
            ]
        ]);
    }

    /**
     * Download attachment
     */
    public function download($filename)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $leaveRequest = DB::table('leave_requests')
            ->where('attachment', 'like', '%' . $filename)
            ->first();

        if (!$leaveRequest) return response()->json(['error' => 'File not found or access denied'], 404);

        if ($user->role === 'employee' && $leaveRequest->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $filePath = storage_path('app/public/' . $leaveRequest->attachment);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found on server'], 404);
        }

        return response()->download($filePath);
    }
}

