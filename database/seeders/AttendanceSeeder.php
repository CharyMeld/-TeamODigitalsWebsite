<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'department' => 'Administration',
                'role' => 'admin'
            ]
        );
        // Create sample employees
        $employees = [
            ['name' => 'John Doe', 'email' => 'john@company.com', 'department' => 'IT'],
            ['name' => 'Jane Smith', 'email' => 'jane@company.com', 'department' => 'HR'],
            ['name' => 'Bob Johnson', 'email' => 'bob@company.com', 'department' => 'Finance'],
            ['name' => 'Alice Brown', 'email' => 'alice@company.com', 'department' => 'Marketing'],
            ['name' => 'Charlie Wilson', 'email' => 'charlie@company.com', 'department' => 'Sales'],
        ];
        foreach ($employees as $employeeData) {
            User::firstOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['name'],
                    'password' => Hash::make('password'),
                    'department' => $employeeData['department'],
                    'role' => 'employee'
                ]
            );
        }
        // Create sample attendance records for the last 7 days
        $users = User::where('role', 'employee')->get();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            foreach ($users as $user) {
                // Skip some records to simulate absent employees
                if (rand(1, 10) <= 2) continue;
                
                $checkIn = $date->copy()->setTime(8, 30)->addMinutes(rand(0, 60));
                $isLate = $checkIn->hour >= 9 && $checkIn->minute > 0;
                
                // Determine if they've checked out (80% chance)
                $hasCheckedOut = rand(1, 10) <= 8;
                $checkOut = null;
                $workingHours = 0; // CHANGED: Initialize to 0 instead of null
                
                if ($hasCheckedOut) {
                    $checkOut = $checkIn->copy()->addHours(8)->addMinutes(rand(-30, 60));
                    $workingMinutes = $checkOut->diffInMinutes($checkIn) - rand(30, 90); // Subtract break time
                    $workingHours = max(0, $workingMinutes);
                }
                
                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'status' => $isLate ? 'late' : 'present',
                    'break_status' => 'none',
                    'total_break_time' => rand(30, 90),
                    'working_hours' => $workingHours // Now always has a value (0 or calculated)
                ]);
            }
        }
    }
}
