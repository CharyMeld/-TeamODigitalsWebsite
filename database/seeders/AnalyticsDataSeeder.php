<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AssignTask;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AnalyticsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define departments
        $departments = ['DeptI','DeptII','DeptIII','DeptIV','DeptV','DeptVI','DeptVII','DeptVIII','DeptIX','DeptX'];
        
        // Create sample users if they don't exist
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate([
                'email' => "employee{$i}@example.com"
            ], [
                'name' => "Employee {$i}",
                'password' => bcrypt('password'),
                'role' => 'employee',
                'status' => 'active',
                'department' => $departments[$i - 1],
                'employee_id' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ]);
            $users[] = $user;
        }

        // Create attendance records for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($users as $user) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) continue;

                $statuses = ['present', 'present', 'present', 'present', 'present', 
                             'present', 'present', 'present', 'present', 'absent'];
                $status = $statuses[array_rand($statuses)];

                // Generate realistic check-in/check-out times
                $checkIn = null;
                $checkOut = null;
                $workingMinutes = 0;
                $breakStatus = 'none';
                
                if ($status === 'present') {
                    $checkInHour = rand(8, 10);
                    $checkInMinute = rand(0, 59);
                    $checkIn = sprintf('%02d:%02d:00', $checkInHour, $checkInMinute);
                    
                    $checkOutHour = rand(16, 18);
                    $checkOutMinute = rand(0, 59);
                    $checkOut = sprintf('%02d:%02d:00', $checkOutHour, $checkOutMinute);
                    
                    // Calculate working minutes (rough estimate)
                    $startTime = Carbon::createFromFormat('H:i:s', $checkIn);
                    $endTime = Carbon::createFromFormat('H:i:s', $checkOut);
                    $workingMinutes = $startTime->diffInMinutes($endTime);
                    
                    // Random break status for present employees
                    $breakStatuses = ['none', 'none', 'none', 'ended']; // Most not on break
                    $breakStatus = $breakStatuses[array_rand($breakStatuses)];
                }

                Attendance::firstOrCreate([
                    'user_id' => $user->id,
                    'date' => $date->toDateString()
                ], [
                    'status' => $status,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'break_status' => $breakStatus, // Use correct ENUM values
                    'total_break_time' => $status === 'present' ? rand(15, 60) : 0, // minutes
                    'working_hours' => $workingMinutes, // Store as minutes
                    'current_break_start' => null,
                    'working_sessions' => null,
                    'notes' => null,
                ]);
            }
        }

        // Create sample tasks
        $taskTitles = [
            'Complete project documentation', 'Review code changes', 'Update user interface',
            'Fix reported bugs', 'Prepare presentation', 'Conduct user testing',
            'Optimize database queries', 'Write unit tests', 'Deploy to production',
            'Update system configuration'
        ];

        foreach ($users as $user) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                $createdAt = Carbon::now()->subDays(rand(1, 30));
                $dueDate = $createdAt->copy()->addDays(rand(1, 14));

                $statuses = ['completed', 'completed', 'completed', 'in_progress', 'pending'];
                $status = $statuses[array_rand($statuses)];

                if ($status !== 'completed' && rand(1, 10) > 8) {
                    $dueDate = Carbon::now()->subDays(rand(1, 5));
                }

                AssignTask::create([
                    'employee_id' => $user->employee_id,
                    'task_title' => $taskTitles[array_rand($taskTitles)],
                    'task_description' => 'Task description for ' . $taskTitles[array_rand($taskTitles)],
                    'status' => $status,
                    'due_date' => $dueDate,
                    'admin_id' => 1,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);
            }
        }

        // Create sample leave requests
        $leaveTypes = ['Annual Leave', 'Sick Leave', 'Emergency Leave', 'Maternity Leave',
                       'Paternity Leave', 'Study Leave', 'Compassionate Leave', 'Medical Leave'];

        $leaveStatuses = ['Pending', 'Approved', 'Approved', 'Approved', 'Declined'];

        foreach ($users as $user) {
            if (rand(1, 10) > 6) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $startDate = Carbon::now()->subDays(rand(1, 60));
                    $endDate = $startDate->copy()->addDays(rand(0, 5));
                    $status = $leaveStatuses[array_rand($leaveStatuses)];
                    $superadmin = $status === 'Approved' ? '1' : null;

                    LeaveRequest::create([
                        'user_id' => $user->id,
                        'employee_name' => $user->name,
                        'employee_id' => $user->employee_id ?? 'EMP-'.$user->id,
                        'department' => $user->department,
                        'job_title' => $user->role,
                        'contact' => $user->address ?? 'N/A',
                        'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'number_of_days' => $startDate->diffInDays($endDate) + 1,
                        'reason' => 'Sample leave request reason',
                        'status' => $status,
                        'superadmin' => $superadmin,
                        'updated_at' => $status === 'Approved' ? $startDate->copy()->subDays(1) : null,
                        'created_at' => $startDate->copy()->subDays(rand(1, 7)),
                    ]);
                }
            }
        }

        $this->command->info('Analytics sample data seeded successfully!');
    }
}
