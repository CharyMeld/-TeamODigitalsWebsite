<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminAttendanceController extends Controller
{
    
    /**
     * Get admin's attendance for a specific date - OPTIMIZED
     */
      public function getMyAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;
        $adminId = Auth::id();

        $attendance = DB::table('attendances')
            ->select('id', 'check_in_time', 'check_out_time', 'current_break_start', 'break_status', 'total_break_time', 'working_hours')
            ->where('user_id', $adminId)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'attendance' => [
                    'id' => null,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'current_break_start' => null,
                    'break_status' => 'none',  // ADD THIS
                    'total_break_time' => 0,
                    'working_hours' => '0.00',
                    'status' => $this->formatStatusResponse('Absent')
                ]
            ]);
        }

        if (!$attendance->working_hours && $attendance->check_in_time && $attendance->check_out_time) {
            $hoursWorked = $this->calculateHoursWorked($attendance);
            DB::table('attendances')->where('id', $attendance->id)->update(['working_hours' => $hoursWorked]);
            $attendance->working_hours = $hoursWorked;
        }

        return response()->json([
            'success' => true,
            'attendance' => [
                'id' => $attendance->id,
                'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : null,
                'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : null,
                'current_break_start' => $attendance->current_break_start ? Carbon::parse($attendance->current_break_start)->format('H:i') : null,
                'break_status' => $attendance->break_status ?? 'none',  // ADD THIS LINE
                'total_break_time' => $attendance->total_break_time ?? 0,
                'working_hours' => $attendance->working_hours ?: '0.00',
                'status' => $this->determineStatusFromRecord($attendance)
            ]
        ]);
    }
    /**
     * Admin sign in 
     */
    public function signIn(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $adminId = Auth::id();
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $existingAttendance = DB::table('attendances')
                ->where('user_id', $adminId)
                ->whereDate('date', $date)
                ->first();

            if ($existingAttendance && $existingAttendance->check_in_time) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You are already signed in for today'], 400);
            }

            if ($existingAttendance) {
                DB::table('attendances')
                    ->where('id', $existingAttendance->id)
                    ->update(['check_in_time' => $now, 'status' => 'present']);
                $attendanceId = $existingAttendance->id;
            } else {
                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $adminId,
                    'date' => $date,
                    'check_in_time' => $now,
                    'status' => 'present',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully signed in',
                'attendance' => [
                    'id' => $attendanceId,
                    'check_in_time' => $now->format('H:i'),
                    'check_out_time' => null,
                    'current_break_start' => null,
                    'total_break_time' => 0,
                    'working_hours' => '0.00',
                    'status' => $this->formatStatusResponse('Present')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to sign in: '.$e->getMessage()], 500);
        }
    }

    /**
     * Admin sign out 
     */
    public function signOut(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $adminId = Auth::id();
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $attendance = DB::table('attendances')
                ->where('user_id', $adminId)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You must sign in first'], 400);
            }

            if ($attendance->check_out_time) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You have already signed out for today'], 400);
            }

            $hoursWorked = $this->calculateHoursWorked($attendance);

            DB::table('attendances')
                ->where('id', $attendance->id)
                ->update([
                    'check_out_time' => $now,
                    'working_hours' => $hoursWorked,
                    'status' => 'present'
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully signed out',
                'attendance' => [
                    'id' => $attendance->id,
                    'check_in_time' => Carbon::parse($attendance->check_in_time)->format('H:i'),
                    'check_out_time' => $now->format('H:i'),
                    'current_break_start' => null,
                    'total_break_time' => $attendance->total_break_time ?? 0,
                    'working_hours' => $hoursWorked,
                    'status' => $this->formatStatusResponse('Signed Out')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to sign out: '.$e->getMessage()], 500);
        }
    }

    /**
     * Start break - OPTIMIZED
     */
    public function startBreak(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->date;
        $adminId = Auth::id();
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $attendance = DB::table('attendances')
                ->where('user_id', $adminId)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You must sign in first before starting a break'
                ], 400);
            }

            if ($attendance->check_out_time) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start break after signing out'
                ], 400);
            }

            if ($attendance->current_break_start) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You are already on a break'
                ], 400);
            }

            DB::table('attendances')
                ->where('id', $attendance->id)
                ->update([
                    'current_break_start' => $now,
                    'break_status' => 'on_break'
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Break started successfully',
                'attendance' => [
                    'id' => $attendance->id,
                    'check_in_time' => Carbon::parse($attendance->check_in_time)->format('H:i'),
                    'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : null,
                    'current_break_start' => $now->format('H:i'),
                    'break_status' => 'on_break',  // ADD THIS LINE
                    'total_break_time' => 0,
                    'working_hours' => $attendance->working_hours ?: '0.00',
                    'status' => $this->formatStatusResponse('On Break')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to start break: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    /**
     * End break 
     */
    public function endBreak(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->date;
        $adminId = Auth::id();
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $attendance = DB::table('attendances')
                ->where('user_id', $adminId)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || !$attendance->current_break_start) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No active break found to end'
                ], 400);
            }

            $breakStart = Carbon::parse($attendance->current_break_start);
            $breakDurationMinutes = $breakStart->diffInMinutes($now);

            DB::table('attendances')
                ->where('id', $attendance->id)
                ->update([
                    'total_break_time' => $breakDurationMinutes,
                    'current_break_start' => null,
                    'break_status' => 'none'
                ]);

            DB::commit();

            $updatedAttendance = DB::table('attendances')->where('id', $attendance->id)->first();

           return response()->json([
            'success' => true,
            'message' => 'Break ended successfully',
            'attendance' => [
                'id' => $updatedAttendance->id,
                'check_in_time' => Carbon::parse($updatedAttendance->check_in_time)->format('H:i'),
                'check_out_time' => $updatedAttendance->check_out_time ? Carbon::parse($updatedAttendance->check_out_time)->format('H:i') : null,
                'current_break_start' => null,
                'break_status' => 'none',  // ADD THIS LINE
                'total_break_time' => $breakDurationMinutes,
                'working_hours' => $updatedAttendance->working_hours ?: '0.00',
                'status' => $this->determineStatusFromRecord($updatedAttendance)
            ]
        ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to end break: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format attendance status with color
     */
    private function formatStatusResponse(string $status): array
    {
        $color = match ($status) {
            'Present'    => 'green',
            'Late'       => 'yellow',
            'On Break'   => 'blue',
            'Signed Out' => 'gray',
            'Absent'     => 'red',
            default      => 'dark',
        };

        return [
            'status' => $status,
            'color' => $color,
        ];
    }

    /**
     * Determine status from a record
     */
      
    private function determineStatusFromRecord($record): array
    {
        if (!$record || empty($record->check_in_time)) {
            return $this->formatStatusResponse('Absent');
        }

        if (!empty($record->check_out_time)) {
            return $this->formatStatusResponse('Signed Out');
        }

        if (!empty($record->break_status) && $record->break_status === 'on_break') {
            return $this->formatStatusResponse('On Break');
        }

        // FIX: Safely check if date property exists before using it
        if (isset($record->date) && !empty($record->check_in_time)) {
            try {
                $checkInTime = Carbon::parse($record->check_in_time);
                $workStartTime = Carbon::parse($record->date)->setTime(8, 0, 0);

                if ($checkInTime->gt($workStartTime)) {
                    return $this->formatStatusResponse('Late');
                }
            } catch (\Exception $e) {
                // If date parsing fails, just return Present
                \Log::warning('Failed to parse date for status determination: ' . $e->getMessage());
            }
        }

        return $this->formatStatusResponse('Present');
    }


    /**
     * Calculate hours worked
     */
    private function calculateHoursWorked($attendance): string
    {
        if (!$attendance->check_in_time) return '0.00';

        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time) : Carbon::now();

        $totalMinutes = $checkIn->diffInMinutes($checkOut);

        $breakMinutes = $attendance->total_break_time ?? 0;

        $workedMinutes = max(0, $totalMinutes - $breakMinutes);

        return number_format($workedMinutes / 60, 2);
    }
    
 
       /**
     * Get all employees' attendance for a specific date - OPTIMIZED
     */
    public function getEmployeesAttendance(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));

            // Cache results for 5 minutes
            $cacheKey = "employees_attendance_{$date}";

            $employeesAttendance = Cache::remember($cacheKey, 300, function () use ($date) {
                $results = DB::table('users as u')
                    ->leftJoin('attendances as a', function ($join) use ($date) {
                        $join->on('u.id', '=', 'a.user_id')
                             ->whereDate('a.date', $date);
                    })
                    ->where('u.role', 'employee')
                    ->orderBy('u.name')
                    ->select(
                        'u.id',
                        'u.name',
                        'u.email',
                        'u.department',
                        'a.id as attendance_id',
                        'a.date',
                        'a.check_in_time',
                        'a.check_out_time',
                        'a.break_status',
                        'a.current_break_start',
                        'a.total_break_time',
                        'a.working_hours',
                        'a.status',
                        'a.working_sessions'
                    )
                    ->get();

                return $results->map(function ($record) {
                    // Determine status string
                    $statusObj = $this->determineStatusFromRecord($record);
                    $statusString = $statusObj['status']; // just the string
                    $statusColor = $statusObj['color'];   // optional

                    return [
                        'id' => $record->id,
                        'attendance_id' => $record->attendance_id,
                        'name' => $record->name,
                        'email' => $record->email,
                        'department' => $record->department ?? 'Not Set',
                        'status' => $statusString,
                        'status_color' => $statusColor,
                        'check_in_time' => $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : null,
                        'check_out_time' => $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : null,
                        'break_status' => $record->break_status ?? 'None',
                        'current_break_start' => $record->current_break_start ? Carbon::parse($record->current_break_start)->format('H:i') : null,
                        'total_break_time' => $record->total_break_time ?? 0,
                        'working_hours' => $record->working_hours ? number_format($record->working_hours, 2) : '0.00',
                        'working_sessions' => $record->working_sessions ?? [],
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'date' => $date,
                'employees' => $employeesAttendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendanceSummary(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));

            $cacheKey = "attendance_summary_{$date}";

            $summary = Cache::remember($cacheKey, 300, function () use ($date) {
                $result = DB::selectOne("
                    SELECT 
                        COUNT(u.id) as total_employees,
                        COUNT(a.check_in_time) as present_count,
                        SUM(CASE WHEN a.break_status = 'on_break' THEN 1 ELSE 0 END) as on_break_count,
                        COUNT(a.check_out_time) as signed_out_count,
                        AVG(a.working_hours) as average_hours
                    FROM users u
                    LEFT JOIN attendances a ON u.id = a.user_id AND DATE(a.date) = ?
                    WHERE u.role = 'employee'
                ", [$date]);

                $absentCount = $result->total_employees - $result->present_count;

                return [
                    'total_employees' => (int)$result->total_employees,
                    'present' => (int)$result->present_count,
                    'absent' => $absentCount,
                    'on_break' => (int)$result->on_break_count,
                    'signed_out' => (int)$result->signed_out_count,
                    'average_hours' => $result->average_hours ? number_format($result->average_hours, 2) : '0.00'
                ];
            });

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attendance summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendanceHistory(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

            $history = DB::table('attendances as a')
                ->join('users as u', 'a.user_id', '=', 'u.id')
                ->whereBetween('a.date', [$startDate, $endDate])
                ->where('u.role', 'employee')
                ->select([
                    'a.id',
                    'u.name',
                    'u.department',
                    'a.date',
                    'a.check_in_time',
                    'a.check_out_time',
                    'a.working_hours',
                    'a.status'
                ])
                ->orderBy('a.date', 'desc')
                ->orderBy('u.name')
                ->limit(100)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'name' => $record->name,
                        'department' => $record->department ?? 'Not Set',
                        'date' => Carbon::parse($record->date)->format('M d, Y'),
                        'check_in_time' => $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : null,
                        'check_out_time' => $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : null,
                        'working_hours' => $record->working_hours ? number_format($record->working_hours, 2) : '0.00',
                        'status' => $record->status ?? $this->determineStatusFromRecord($record)
                    ];
                });

            return response()->json([
                'success' => true,
                'history' => $history,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attendance history',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function myAttendance(Request $request)
    {
        try {
            $date = $request->input('date', Carbon::today()->toDateString());

            $attendance = Attendance::where('user_id', Auth::id())
                ->whereDate('date', $date)
                ->first();

            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'status' => $attendance ? $this->determineStatusFromRecord($attendance) : 'Absent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
         
    private function determineStatus($attendance): string
    {
        if (!$attendance || !$attendance->check_in_time) {
            return 'Not Signed In';
        }

        if ($attendance->check_out_time) {
            return 'Signed Out';
        }

        if ($attendance->current_break_start && !$attendance->total_break_time) {
            return 'On Break';
        }

        return 'Present';
    }
    
}
