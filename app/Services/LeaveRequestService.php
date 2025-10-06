<?php
/*
|--------------------------------------------------------------------------
| Leave Request Service Class
|--------------------------------------------------------------------------
*/

// app/Services/LeaveRequestService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestService
{
    /**
     * Calculate business days between two dates (excluding weekends)
     */
    public static function calculateBusinessDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $businessDays = 0;
        
        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $businessDays++;
            }
            $start->addDay();
        }
        
        return $businessDays;
    }

    /**
     * Check if user has exceeded leave quota
     */
    public static function checkLeaveQuota($userId, $leaveType, $requestedDays)
    {
        // Define leave quotas (you can move this to config or database)
        $quotas = [
            'Annual Leave' => 21,
            'Sick Leave' => 10,
            'Emergency Leave' => 3,
            'Study Leave' => 5,
            // Add more as needed
        ];

        if (!isset($quotas[$leaveType])) {
            return true; // No limit for this type
        }

        // Get used days for this year
        $usedDays = DB::table('leave_requests')
            ->where('user_id', $userId)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('number_of_days');

        $totalAfterRequest = $usedDays + $requestedDays;

        return $totalAfterRequest <= $quotas[$leaveType];
    }

    /**
     * Check for overlapping leave requests
     */
    public static function hasOverlappingRequests($userId, $startDate, $endDate, $excludeId = null)
    {
        $query = DB::table('leave_requests')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Send notification emails
     */
    public static function sendNotification($type, $leaveRequest, $additionalData = [])
    {
        try {
            switch ($type) {
                case 'new_request':
                    // Notify admin/superadmin of new request
                    $admins = DB::table('users')
                        ->whereIn('role', ['admin', 'superadmin'])
                        ->get();
                    
                    foreach ($admins as $admin) {
                        // Mail::to($admin->email)->send(new NewLeaveRequestNotification($leaveRequest));
                    }
                    break;

                case 'status_update':
                    // Notify employee of status change
                    $employee = DB::table('users')->where('id', $leaveRequest->user_id)->first();
                    if ($employee) {
                        // Mail::to($employee->email)->send(new LeaveStatusUpdateNotification($leaveRequest, $additionalData));
                    }
                    break;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Failed to send leave notification: ' . $e->getMessage());
        }
    }

    /**
     * Generate leave request report
     */
    public static function generateReport($filters = [])
    {
        $query = DB::table('leave_requests')
            ->select([
                'id', 'employee_name', 'employee_id', 'department', 
                'leave_type', 'start_date', 'end_date', 'number_of_days',
                'status', 'created_at'
            ]);

        // Apply filters
        if (isset($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Clean up old attachments
     */
    public static function cleanupOldAttachments()
    {
        $oldRequests = DB::table('leave_requests')
            ->where('created_at', '<', now()->subYears(2))
            ->whereNotNull('attachment')
            ->get();

        foreach ($oldRequests as $request) {
            if (Storage::disk('public')->exists($request->attachment)) {
                Storage::disk('public')->delete($request->attachment);
            }
        }

        return $oldRequests->count();
    }
}

