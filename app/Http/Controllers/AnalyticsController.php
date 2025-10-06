<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');

        $dateRange = $this->getDateRange($period);

        return response()->json([
            'attendanceStats' => $this->getAttendanceStats($dateRange),
            'taskStats' => $this->getTaskStats($dateRange),
            'leaveStats' => $this->getLeaveStats($dateRange),
            'performanceData' => $this->getPerformanceData($dateRange, $period)
        ]);
    }

    private function getDateRange($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'week':
                return ['start' => $now->startOfWeek()->toDateString(), 'end' => $now->endOfWeek()->toDateString()];
            case 'quarter':
                return ['start' => $now->startOfQuarter()->toDateString(), 'end' => $now->endOfQuarter()->toDateString()];
            case 'year':
                return ['start' => $now->startOfYear()->toDateString(), 'end' => $now->endOfYear()->toDateString()];
            default: // month
                return ['start' => $now->startOfMonth()->toDateString(), 'end' => $now->endOfMonth()->toDateString()];
        }
    }

    private function getAttendanceStats($dateRange)
    {
        $today = Carbon::today()->toDateString();

        $totalEmployees = DB::table('users')
            ->where('role', 'employee')
            ->where('status', 'active')
            ->count();

        $todayAttendance = DB::table('attendances')->whereDate('date', $today)->get();

        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        $onBreak = $todayAttendance->where('break_status', 'on_break')->count();

        $avgHoursWorked = DB::table('attendances')
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'present')
            ->whereNotNull('check_out_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, CONCAT(date," ",check_in_time), CONCAT(date," ",check_out_time))) as avg_hours')
            ->value('avg_hours') ?? 0;

        return [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'onBreak' => $onBreak,
            'avgHoursWorked' => round($avgHoursWorked, 1)
        ];
    }

    private function getTaskStats($dateRange)
    {
        $taskStats = DB::table('assign_tasks')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status IN ("in_progress","pending") THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status != "completed" AND due_date < NOW() THEN 1 ELSE 0 END) as overdue_tasks
            ')
            ->first();

        return [
            'totalTasks' => $taskStats->total_tasks ?? 0,
            'completedTasks' => $taskStats->completed_tasks ?? 0,
            'pendingTasks' => $taskStats->pending_tasks ?? 0,
            'overdueTasks' => $taskStats->overdue_tasks ?? 0
        ];
    }

    private function getLeaveStats($dateRange)
    {
        $leaveStats = DB::table('leave_requests')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as rejected_requests
            ')
            ->first();

        return [
            'totalRequests' => $leaveStats->total_requests ?? 0,
            'approvedRequests' => $leaveStats->approved_requests ?? 0,
            'pendingRequests' => $leaveStats->pending_requests ?? 0,
            'rejectedRequests' => $leaveStats->rejected_requests ?? 0
        ];
    }

    private function getPerformanceData($dateRange, $period)
    {
        $performanceData = [];

        if ($period === 'year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $performanceData[] = [
                    'period' => $monthStart->format('M'),
                    'attendance' => $this->getAttendanceRateForPeriod($monthStart, $monthEnd),
                    'taskCompletion' => $this->getTaskCompletionRateForPeriod($monthStart, $monthEnd)
                ];
            }
        } else {
            $startDate = Carbon::parse($dateRange['start']);
            $endDate = Carbon::parse($dateRange['end']);

            while ($startDate->lte($endDate)) {
                $weekEnd = $startDate->copy()->endOfWeek();
                if ($weekEnd->gt($endDate)) $weekEnd = $endDate;

                $performanceData[] = [
                    'period' => $startDate->format('M d'),
                    'attendance' => $this->getAttendanceRateForPeriod($startDate, $weekEnd),
                    'taskCompletion' => $this->getTaskCompletionRateForPeriod($startDate, $weekEnd)
                ];

                $startDate->addWeek();
            }
        }

        return $performanceData;
    }

    private function getAttendanceRateForPeriod($start, $end)
    {
        $totalDays = DB::table('attendances')->whereBetween('date', [$start->toDateString(), $end->toDateString()])->count();
        $presentDays = DB::table('attendances')->whereBetween('date', [$start->toDateString(), $end->toDateString()])->where('status', 'present')->count();
        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
    }

    private function getTaskCompletionRateForPeriod($start, $end)
    {
        $totalTasks = DB::table('assign_tasks')->whereBetween('created_at', [$start, $end])->count();
        $completedTasks = DB::table('assign_tasks')->whereBetween('created_at', [$start, $end])->where('status', 'completed')->count();
        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'pdf');

        $data = [
            'attendanceStats' => $this->getAttendanceStats($this->getDateRange($period)),
            'taskStats' => $this->getTaskStats($this->getDateRange($period)),
            'leaveStats' => $this->getLeaveStats($this->getDateRange($period)),
            'performanceData' => $this->getPerformanceData($this->getDateRange($period), $period),
            'period' => $period,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        if ($format === 'pdf') {
            return response()->json($data)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="analytics-report.json"');
        }

        return response()->json($data);
    }

    public function emailReport(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'period' => 'nullable|string|in:week,month,quarter,year'
        ]);

        $email = $request->input('email');
        $period = $request->input('period', 'month');

        try {
            $dateRange = $this->getDateRange($period);

            $data = [
                'attendanceStats' => $this->getAttendanceStats($dateRange),
                'taskStats' => $this->getTaskStats($dateRange),
                'leaveStats' => $this->getLeaveStats($dateRange),
                'performanceData' => $this->getPerformanceData($dateRange, $period),
                'period' => ucfirst($period),
                'generated_at' => Carbon::now()->format('F d, Y h:i A')
            ];

            // Send email
            \Illuminate\Support\Facades\Mail::send('emails.analytics-report', $data, function ($message) use ($email, $period) {
                $message->to($email)
                        ->subject('Analytics Report - ' . ucfirst($period) . ' Performance')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            // Log the email for debugging
            \Log::info('Analytics report email sent', [
                'to' => $email,
                'period' => $period,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report sent successfully to ' . $email
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send analytics report email', [
                'error' => $e->getMessage(),
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}

