<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use App\Mail\AnalyticsReportMail;
use Barryvdh\DomPDF\Facade\Pdf;




class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $dateRange = $this->getDateRange($period);

            \Log::info('Analytics Dashboard Request', [
                'period' => $period,
                'dateRange' => $dateRange
            ]);

            $response = [
                'attendanceStats' => $this->getAttendanceStats($dateRange),
                'taskStats' => $this->getTaskStats($dateRange),
                'leaveStats' => $this->getLeaveStats($dateRange),
                'performanceData' => $this->getPerformanceData($dateRange, $period)
            ];

            \Log::info('Analytics Response', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Analytics Dashboard Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch analytics data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

   
   public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'excel');
        $dateRange = $this->getDateRange($period);

        $data = [
            'attendanceStats' => $this->getAttendanceStats($dateRange),
            'taskStats' => $this->getTaskStats($dateRange),
            'leaveStats' => $this->getLeaveStats($dateRange),
            'performanceData' => $this->getPerformanceData($dateRange, $period),
            'period' => $period,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        if ($format === 'excel') {
            $filename = "analytics-report-{$period}-" . Carbon::now()->format('Y-m-d') . ".xlsx";
            return Excel::download(new AnalyticsReportExport($data, $period), $filename);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.analytics-pdf', compact('data', 'period'));
            $filename = "analytics-report-{$period}-" . Carbon::now()->format('Y-m-d') . ".pdf";
            return $pdf->download($filename);
        }

        return response()->json($data);
    }

    public function emailReport(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'period' => 'required|string'
        ]);

        $period = $request->get('period', 'month');
        $email = $request->get('email');
        $dateRange = $this->getDateRange($period);

        $data = [
            'attendanceStats' => $this->getAttendanceStats($dateRange),
            'taskStats' => $this->getTaskStats($dateRange),
            'leaveStats' => $this->getLeaveStats($dateRange),
            'performanceData' => $this->getPerformanceData($dateRange, $period),
            'period' => ucfirst($period),
            'generated_at' => Carbon::now()->format('F d, Y h:i A')
        ];

        try {
            \Log::info('Attempting to send analytics report email', [
                'to' => $email,
                'period' => $period,
                'timestamp' => now()
            ]);

            // Try to generate Excel file and send with attachment
            try {
                $filename = "analytics-report-{$period}-" . Carbon::now()->format('Y-m-d') . ".xlsx";
                $excelFile = Excel::raw(new AnalyticsReportExport($data, $period), \Maatwebsite\Excel\Excel::XLSX);

                // Send email with attachment
                Mail::to($email)->send(new AnalyticsReportMail($data, $period, $excelFile, $filename));

                \Log::info('Analytics report email with Excel attachment sent successfully', [
                    'to' => $email,
                    'period' => $period
                ]);
            } catch (\Exception $excelError) {
                \Log::warning('Failed to generate Excel, sending email without attachment', [
                    'error' => $excelError->getMessage()
                ]);

                // Send email without Excel attachment using simple template
                Mail::send('emails.analytics-report', [
                    'data' => $data,
                    'period' => ucfirst($period),
                    'generatedAt' => $data['generated_at']
                ], function ($message) use ($email, $period) {
                    $message->to($email)
                            ->subject('Analytics Report - ' . ucfirst($period) . ' Performance')
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });

                \Log::info('Analytics report email sent successfully without attachment', [
                    'to' => $email,
                    'period' => $period
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Report sent successfully to ' . $email
            ]);
        } catch (\Exception $e) {
            \Log::error('Email Report Error', [
                'error' => $e->getMessage(),
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    public function sendAnalyticsReport(Request $request)
    {
        $data = $request->input('data');
        $period = $request->input('period');
        $excelFile = $request->input('excel_file');
        $filename = "report-" . time() . ".xlsx";

        Mail::to('recipient@example.com')
            ->send(new AnalyticsReportMail($data, $period, $excelFile, $filename));

        return response()->json(['message' => 'Report email sent successfully']);
    }

    public function scheduleReport(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'frequency' => 'required|in:daily,weekly,monthly',
            'period' => 'required|string'
        ]);

        // Store scheduled report in database
        DB::table('scheduled_reports')->insert([
            'email' => $request->email,
            'frequency' => $request->frequency,
            'period' => $request->period,
            'created_at' => now(),
            'updated_at' => now(),
            'next_run' => $this->calculateNextRun($request->frequency),
            'is_active' => true
        ]);

        return response()->json(['message' => 'Report scheduled successfully']);
    }
    
    public function generateCustomReport(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'period' => 'required|in:week,month,quarter,year,custom',
                'metrics' => 'required|array',
                'format' => 'required|in:excel,pdf',
                'filters' => 'nullable|array',
                'filters.dateRange.start' => 'nullable|date',
                'filters.dateRange.end' => 'nullable|date|after_or_equal:filters.dateRange.start',
            ]);

            // Get date range
            $startDate = $validated['filters']['dateRange']['start'] ?? null;
            $endDate = $validated['filters']['dateRange']['end'] ?? null;
            $dateRange = $this->getDateRange($validated['period'], $startDate, $endDate);

            // Build data based on selected metrics
            $data = [
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'report_name' => $validated['name'],
                'period' => $validated['period'],
            ];

            // Only include requested metrics
            $selectedMetrics = $validated['metrics'];
            if (in_array('attendance', $selectedMetrics)) {
                $data['attendanceStats'] = $this->getAttendanceStats($dateRange);
            }
            if (in_array('tasks', $selectedMetrics)) {
                $data['taskStats'] = $this->getTaskStats($dateRange);
            }
            if (in_array('leaves', $selectedMetrics)) {
                $data['leaveStats'] = $this->getLeaveStats($dateRange);
            }
            if (in_array('hours', $selectedMetrics)) {
                // Average hours is already in attendance stats, but we can add it separately if needed
                if (!isset($data['attendanceStats'])) {
                    $data['attendanceStats'] = $this->getAttendanceStats($dateRange);
                }
                $data['hoursStats'] = [
                    'avgHoursWorked' => $data['attendanceStats']['avgHoursWorked'] ?? 0
                ];
            }

            // Always include performance data for charts
            $data['performanceData'] = [];

            // Generate file based on format
            $fileName = str_replace(' ', '-', strtolower($validated['name'])) . '_' . now()->format('Y-m-d_H-i-s');

            if ($validated['format'] === 'excel') {
                $fileName .= '.xlsx';
                return Excel::download(new AnalyticsReportExport($data, $validated['period']), $fileName);
            } else {
                // PDF format - not implemented yet, return Excel for now
                $fileName .= '.xlsx';
                return Excel::download(new AnalyticsReportExport($data, $validated['period']), $fileName);
            }

        } catch (\Exception $e) {
            \Log::error('Error generating custom report: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Failed to generate custom report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function scheduleCustomReport(Request $request)
    {
        $data = $request->all();

        // validate scheduling info
        $request->validate([
            'schedule.enabled' => 'required|boolean',
            'schedule.frequency' => 'nullable|string|in:daily,weekly,monthly',
            'schedule.email' => 'nullable|email',
        ]);

        // Here you’d save scheduling info to DB or queue a job
        // Example response:
        return response()->json([
            'message' => 'Custom report scheduled successfully',
            'schedule' => $data['schedule']
        ], 200);
    }


    private function calculateNextRun($frequency)
    {
        $now = Carbon::now();
        
        return match($frequency) {
            'daily' => $now->addDay(),
            'weekly' => $now->addWeek(),
            'monthly' => $now->addMonth(),
            default => $now->addDay()
        };
    }

     private function getDateRange($period, $startDate = null, $endDate = null)
    {
        // If custom date range provided, use it
        if ($period === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->format('Y-m-d'),
                'end' => Carbon::parse($endDate)->format('Y-m-d')
            ];
        }

        $now = Carbon::now();

        return match($period) {
            'week' => [
                'start' => $now->copy()->startOfWeek()->format('Y-m-d'),
                'end' => $now->copy()->endOfWeek()->format('Y-m-d')
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter()->format('Y-m-d'),
                'end' => $now->copy()->endOfQuarter()->format('Y-m-d')
            ],
            'year' => [
                'start' => $now->copy()->startOfYear()->format('Y-m-d'),
                'end' => $now->copy()->endOfYear()->format('Y-m-d')
            ],
            default => [
                'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $now->copy()->endOfMonth()->format('Y-m-d')
            ]
        };
    }

    private function getAttendanceStats($dateRange)
    {
        $today = Carbon::today()->format('Y-m-d');

        // Total active employees
        $totalEmployees = DB::table('users')
            ->where('role', 'employee')
            ->where('status', 'active')
            ->count();

        // Today's attendance - get all records for today
        $todayAttendance = DB::table('attendances')
            ->whereDate('date', $today)
            ->get();

        // Count by status (matching your database enum values)
        $presentToday = $todayAttendance->whereIn('status', ['present', 'late', 'half_day'])->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        
        // Count people currently on break
        $onBreak = $todayAttendance->where('break_status', 'on_break')->count();

        // Calculate average working hours (convert minutes to hours if stored in minutes)
        $avgHoursWorked = DB::table('attendances')
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->whereNotNull('check_out_time')
            ->where('working_hours', '>', 0)
            ->avg('working_hours') ?? 0;

        // If working_hours is stored in minutes, convert to hours
        $avgHoursWorked = $avgHoursWorked > 24 ? round($avgHoursWorked / 60, 1) : round($avgHoursWorked, 1);

        return [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'onBreak' => $onBreak,
            'avgHoursWorked' => $avgHoursWorked
        ];
    }

    private function getTaskStats($dateRange)
    {
        $taskStats = DB::table('assign_tasks')
            ->whereBetween('created_at', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status IN ("pending", "in_progress", "Submitted") THEN 1 ELSE 0 END) as pending_tasks,
                SUM(CASE WHEN status != "completed" AND (due_date < CURDATE() OR deadline < CURDATE()) THEN 1 ELSE 0 END) as overdue_tasks
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
            ->whereBetween('created_at', [$dateRange['start'] . ' 00:00:00', $dateRange['end'] . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = "Declined" THEN 1 ELSE 0 END) as rejected_requests
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
        $totalRecords = DB::table('attendances')
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->count();
            
        $presentRecords = DB::table('attendances')
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->count();

        return $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100) : 0;
    }

    private function getTaskCompletionRateForPeriod($start, $end)
    {
        $totalTasks = DB::table('assign_tasks')
            ->whereBetween('created_at', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')])
            ->count();
            
        $completedTasks = DB::table('assign_tasks')
            ->whereBetween('created_at', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')])
            ->where('status', 'completed')
            ->count();

        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    }

}

