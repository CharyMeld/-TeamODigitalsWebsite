<?php

namespace App\Http\Controllers\Api;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsReportExport implements WithMultipleSheets
{
    protected $data;
    protected $period;

    public function __construct($data, $period)
    {
        $this->data = $data ?? [];
        $this->period = $period;
    }

    public function sheets(): array
    {
        return [
            'Summary' => new SummarySheet($this->data, $this->period),
            'Attendance' => new AttendanceSheet($this->data),
            'Tasks' => new TasksSheet($this->data),
            'Leave Requests' => new LeaveSheet($this->data),
            'Performance Trends' => new PerformanceSheet($this->data),
        ];
    }
}

class SummarySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $period;

    public function __construct($data, $period)
    {
        $this->data = $data ?? [];
        $this->period = $period;
    }

    public function array(): array
    {
        $attendanceStats = $this->data['attendanceStats'] ?? [];
        $taskStats = $this->data['taskStats'] ?? [];
        $leaveStats = $this->data['leaveStats'] ?? [];

        $attendanceRate = ($attendanceStats['totalEmployees'] ?? 0) > 0
            ? round((($attendanceStats['presentToday'] ?? 0) / ($attendanceStats['totalEmployees'] ?? 0)) * 100, 2)
            : 0;

        $taskCompletionRate = ($taskStats['totalTasks'] ?? 0) > 0
            ? round((($taskStats['completedTasks'] ?? 0) / ($taskStats['totalTasks'] ?? 0)) * 100, 2)
            : 0;

        $leaveApprovalRate = ($leaveStats['totalRequests'] ?? 0) > 0
            ? round((($leaveStats['approvedRequests'] ?? 0) / ($leaveStats['totalRequests'] ?? 0)) * 100, 2)
            : 0;

        return [
            ['Report Period', ucfirst($this->period)],
            ['Generated At', $this->data['generated_at'] ?? now()->toDateTimeString()],
            [''],
            ['ATTENDANCE SUMMARY'],
            ['Total Employees', $attendanceStats['totalEmployees'] ?? 0],
            ['Present Today', $attendanceStats['presentToday'] ?? 0],
            ['Absent Today', $attendanceStats['absentToday'] ?? 0],
            ['On Break', $attendanceStats['onBreak'] ?? 0],
            ['Attendance Rate', $attendanceRate . '%'],
            ['Average Hours Worked', ($attendanceStats['avgHoursWorked'] ?? 0) . ' hours'],
            [''],
            ['TASK SUMMARY'],
            ['Total Tasks', $taskStats['totalTasks'] ?? 0],
            ['Completed Tasks', $taskStats['completedTasks'] ?? 0],
            ['Pending Tasks', $taskStats['pendingTasks'] ?? 0],
            ['Overdue Tasks', $taskStats['overdueTasks'] ?? 0],
            ['Task Completion Rate', $taskCompletionRate . '%'],
            [''],
            ['LEAVE SUMMARY'],
            ['Total Requests', $leaveStats['totalRequests'] ?? 0],
            ['Approved Requests', $leaveStats['approvedRequests'] ?? 0],
            ['Pending Requests', $leaveStats['pendingRequests'] ?? 0],
            ['Rejected Requests', $leaveStats['rejectedRequests'] ?? 0],
            ['Leave Approval Rate', $leaveApprovalRate . '%'],
        ];
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => '2563eb']]],
            12 => ['font' => ['bold' => true, 'color' => ['rgb' => '059669']]],
            19 => ['font' => ['bold' => true, 'color' => ['rgb' => 'd97706']]],
        ];
    }
}

class AttendanceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data ?? [];
    }

    public function array(): array
    {
        $attendanceStats = $this->data['attendanceStats'] ?? [];

        return [
            ['Present Today', $attendanceStats['presentToday'] ?? 0],
            ['Absent Today', $attendanceStats['absentToday'] ?? 0],
            ['On Break', $attendanceStats['onBreak'] ?? 0],
            ['Total Employees', $attendanceStats['totalEmployees'] ?? 0],
            ['Average Hours Worked', ($attendanceStats['avgHoursWorked'] ?? 0) . ' hours'],
        ];
    }

    public function headings(): array
    {
        return ['Status', 'Count'];
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class TasksSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data ?? [];
    }

    public function array(): array
    {
        $taskStats = $this->data['taskStats'] ?? [];

        return [
            ['Completed', $taskStats['completedTasks'] ?? 0],
            ['Pending', $taskStats['pendingTasks'] ?? 0],
            ['Overdue', $taskStats['overdueTasks'] ?? 0],
            ['Total', $taskStats['totalTasks'] ?? 0],
        ];
    }

    public function headings(): array
    {
        return ['Status', 'Count'];
    }

    public function title(): string
    {
        return 'Tasks';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class LeaveSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data ?? [];
    }

    public function array(): array
    {
        $leaveStats = $this->data['leaveStats'] ?? [];

        return [
            ['Approved', $leaveStats['approvedRequests'] ?? 0],
            ['Pending', $leaveStats['pendingRequests'] ?? 0],
            ['Rejected', $leaveStats['rejectedRequests'] ?? 0],
            ['Total', $leaveStats['totalRequests'] ?? 0],
        ];
    }

    public function headings(): array
    {
        return ['Status', 'Count'];
    }

    public function title(): string
    {
        return 'Leave Requests';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class PerformanceSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data ?? [];
    }

    public function array(): array
    {
        $performanceData = $this->data['performanceData'] ?? [];
        $result = [];

        foreach ($performanceData as $item) {
            $result[] = [
                $item['period'] ?? '',
                ($item['attendance'] ?? 0) . '%',
                ($item['taskCompletion'] ?? 0) . '%'
            ];
        }

        return $result;
    }

    public function headings(): array
    {
        return ['Period', 'Attendance Rate', 'Task Completion Rate'];
    }

    public function title(): string
    {
        return 'Performance Trends';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

