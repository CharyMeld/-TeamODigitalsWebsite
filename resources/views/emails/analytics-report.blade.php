<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }
        .stat-card .stat-label {
            font-size: 12px;
            color: #666;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .stat-row span:first-child {
            color: #666;
        }
        .stat-row span:last-child {
            font-weight: bold;
            color: #333;
        }
        .performance-section {
            margin-top: 30px;
        }
        .performance-section h3 {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .performance-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .performance-item .period {
            font-weight: bold;
            color: #333;
        }
        .performance-item .metrics {
            display: flex;
            gap: 20px;
        }
        .performance-item .metric {
            text-align: center;
        }
        .performance-item .metric-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        .performance-item .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .performance-item {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>📊 Analytics Report</h1>
            <p>{{ ucfirst($period) }} Performance Overview</p>
            <p style="font-size: 12px; margin-top: 10px;">Generated on {{ $generatedAt ?? $data['generated_at'] ?? now()->format('F d, Y h:i A') }}</p>
        </div>

        <div class="email-body">
            <p>Hello,</p>
            <p>Here's your {{ strtolower($period) }} analytics report with key performance metrics:</p>

            <div class="stats-grid">
                <!-- Attendance Stats -->
                <div class="stat-card">
                    <h3>👥 Attendance</h3>
                    <div class="stat-row">
                        <span>Total Employees:</span>
                        <span>{{ $data['attendanceStats']['totalEmployees'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Present Today:</span>
                        <span>{{ $data['attendanceStats']['presentToday'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Absent Today:</span>
                        <span>{{ $data['attendanceStats']['absentToday'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>On Break:</span>
                        <span>{{ $data['attendanceStats']['onBreak'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Avg Hours:</span>
                        <span>{{ $data['attendanceStats']['avgHoursWorked'] ?? 0 }}h</span>
                    </div>
                </div>

                <!-- Task Stats -->
                <div class="stat-card">
                    <h3>✅ Tasks</h3>
                    <div class="stat-row">
                        <span>Total Tasks:</span>
                        <span>{{ $data['taskStats']['totalTasks'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Completed:</span>
                        <span style="color: #28a745;">{{ $data['taskStats']['completedTasks'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Pending:</span>
                        <span style="color: #ffc107;">{{ $data['taskStats']['pendingTasks'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Overdue:</span>
                        <span style="color: #dc3545;">{{ $data['taskStats']['overdueTasks'] ?? 0 }}</span>
                    </div>
                </div>

                <!-- Leave Stats -->
                <div class="stat-card">
                    <h3>🏖️ Leave Requests</h3>
                    <div class="stat-row">
                        <span>Total Requests:</span>
                        <span>{{ $data['leaveStats']['totalRequests'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Approved:</span>
                        <span style="color: #28a745;">{{ $data['leaveStats']['approvedRequests'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Pending:</span>
                        <span style="color: #ffc107;">{{ $data['leaveStats']['pendingRequests'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Rejected:</span>
                        <span style="color: #dc3545;">{{ $data['leaveStats']['rejectedRequests'] ?? 0 }}</span>
                    </div>
                </div>

                <!-- Performance Summary -->
                <div class="stat-card">
                    <h3>📈 Performance</h3>
                    @php
                        $performanceData = $data['performanceData'] ?? [];
                        $totalAttendance = 0;
                        $totalTaskCompletion = 0;
                        $count = count($performanceData);
                        if ($count > 0) {
                            foreach ($performanceData as $item) {
                                $totalAttendance += $item['attendance'] ?? 0;
                                $totalTaskCompletion += $item['taskCompletion'] ?? 0;
                            }
                            $avgAttendance = round($totalAttendance / $count);
                            $avgTaskCompletion = round($totalTaskCompletion / $count);
                        } else {
                            $avgAttendance = 0;
                            $avgTaskCompletion = 0;
                        }
                    @endphp
                    <div class="stat-row">
                        <span>Avg Attendance:</span>
                        <span>{{ $avgAttendance }}%</span>
                    </div>
                    <div class="stat-row">
                        <span>Avg Task Completion:</span>
                        <span>{{ $avgTaskCompletion }}%</span>
                    </div>
                    <div class="stat-row">
                        <span>Reporting Period:</span>
                        <span>{{ ucfirst($period) }}</span>
                    </div>
                </div>
            </div>

            @if(!empty($data['performanceData']) && count($data['performanceData']) > 0)
            <div class="performance-section">
                <h3>📊 Performance Trends</h3>
                @foreach($data['performanceData'] as $item)
                <div class="performance-item">
                    <div class="period">{{ $item['period'] ?? 'N/A' }}</div>
                    <div class="metrics">
                        <div class="metric">
                            <div class="metric-label">Attendance</div>
                            <div class="metric-value">{{ $item['attendance'] ?? 0 }}%</div>
                        </div>
                        <div class="metric">
                            <div class="metric-label">Task Completion</div>
                            <div class="metric-value">{{ $item['taskCompletion'] ?? 0 }}%</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <p style="margin-top: 30px;">This is an automated report. For detailed analytics, please log in to your dashboard.</p>
        </div>

        <div class="email-footer">
            <p><strong>Teamo Digital Solutions</strong></p>
            <p>Employee Management System</p>
            <p style="margin-top: 10px; color: #999;">This email was sent automatically. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
