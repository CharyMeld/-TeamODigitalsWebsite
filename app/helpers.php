<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

// app/helpers.php (or add to existing helpers file)

if (!function_exists('getLeaveBalance')) {
    function getLeaveBalance($userId, $leaveType, $year = null) {
        $year = $year ?? now()->year;
        
        $quotas = config('leave.quotas', [
            'Annual Leave' => 21,
            'Sick Leave' => 10,
            'Emergency Leave' => 3,
        ]);

        $quota = $quotas[$leaveType] ?? 0;

        $used = DB::table('leave_requests')
            ->where('user_id', $userId)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('number_of_days');

        return [
            'quota' => $quota,
            'used' => $used,
            'remaining' => max(0, $quota - $used)
        ];
    }
}

if (!function_exists('formatLeaveStatus')) {
    function formatLeaveStatus($status) {
        $statuses = [
            'pending' => ['label' => 'Pending', 'class' => 'warning'],
            'approved' => ['label' => 'Approved', 'class' => 'success'],
            'declined' => ['label' => 'Declined', 'class' => 'danger'],
        ];

        return $statuses[$status] ?? ['label' => ucfirst($status), 'class' => 'secondary'];
    }
}
