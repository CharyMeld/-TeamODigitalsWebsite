<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report - {{ ucfirst($period) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Analytics Report - {{ ucfirst($period) }}</h1>
    <p><strong>Generated At:</strong> {{ $data['generated_at'] }}</p>

    <h2>Attendance Stats</h2>
    <table>
        <tr><th>Total Employees</th><td>{{ $data['attendanceStats']['totalEmployees'] }}</td></tr>
        <tr><th>Present Today</th><td>{{ $data['attendanceStats']['presentToday'] }}</td></tr>
        <tr><th>Absent Today</th><td>{{ $data['attendanceStats']['absentToday'] }}</td></tr>
        <tr><th>On Break</th><td>{{ $data['attendanceStats']['onBreak'] }}</td></tr>
        <tr><th>Average Hours Worked</th><td>{{ $data['attendanceStats']['avgHoursWorked'] }}</td></tr>
    </table>

    <h2>Task Stats</h2>
    <table>
        <tr><th>Total Tasks</th><td>{{ $data['taskStats']['totalTasks'] }}</td></tr>
        <tr><th>Completed Tasks</th><td>{{ $data['taskStats']['completedTasks'] }}</td></tr>
        <tr><th>Pending Tasks</th><td>{{ $data['taskStats']['pendingTasks'] }}</td></tr>
        <tr><th>Overdue Tasks</th><td>{{ $data['taskStats']['overdueTasks'] }}</td></tr>
    </table>

    <h2>Leave Stats</h2>
    <table>
        <tr><th>Total Requests</th><td>{{ $data['leaveStats']['totalRequests'] }}</td></tr>
        <tr><th>Approved</th><td>{{ $data['leaveStats']['approvedRequests'] }}</td></tr>
        <tr><th>Pending</th><td>{{ $data['leaveStats']['pendingRequests'] }}</td></tr>
        <tr><th>Rejected</th><td>{{ $data['leaveStats']['rejectedRequests'] }}</td></tr>
    </table>

    <h2>Performance Data</h2>
    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Attendance %</th>
                <th>Task Completion %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['performanceData'] as $row)
                <tr>
                    <td>{{ $row['period'] }}</td>
                    <td>{{ $row['attendance'] }}%</td>
                    <td>{{ $row['taskCompletion'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

