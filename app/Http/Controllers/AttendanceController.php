<?php

// app/Http/Controllers/AttendanceController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function getAttendance(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            
            $attendance = DB::table('attendances')
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->select([
                    'attendances.id',
                    'users.name',
                    'users.department',
                    'attendances.status',
                    'attendances.check_in_time',
                    'attendances.check_out_time',
                    'attendances.total_break_time',
                    'attendances.working_hours'
                ])
                ->whereDate('attendances.date', $date)
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'name' => $record->name,
                        'department' => $record->department,
                        'status' => $this->getStatusLabel($record->status),
                        'checkIn' => $record->check_in_time ? Carbon::parse($record->check_in_time)->format('H:i') : null,
                        'checkOut' => $record->check_out_time ? Carbon::parse($record->check_out_time)->format('H:i') : null,
                        'workingHours' => $this->formatDuration($record->working_hours),
                        'breakTime' => $this->formatDuration($record->total_break_time)
                    ];
                });

            return response()->json(['attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAdminAttendance(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $user = Auth::user();
            
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'isCheckedIn' => false,
                    'checkInTime' => null,
                    'checkOutTime' => null,
                    'isOnBreak' => false,
                    'breakStartTime' => null,
                    'totalBreakTime' => 0,
                    'workingSessions' => []
                ]);
            }

            return response()->json([
                'isCheckedIn' => $attendance->status !== 'absent' && !$attendance->check_out_time,
                'checkInTime' => $attendance->check_in_time,
                'checkOutTime' => $attendance->check_out_time,
                'isOnBreak' => $attendance->break_status === 'on_break',
                'breakStartTime' => $attendance->current_break_start,
                'totalBreakTime' => $attendance->total_break_time ?? 0,
                'workingSessions' => json_decode($attendance->working_sessions ?? '[]')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch admin attendance',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminCheckIn(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $timestamp = Carbon::parse($request->get('timestamp', now()));

            // Check if already checked in today
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if ($existingAttendance && $existingAttendance->check_in_time) {
                return response()->json([
                    'error' => 'Already checked in today'
                ], 400);
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date
                ],
                [
                    'check_in_time' => $timestamp,
                    'status' => $this->determineStatus($timestamp),
                    'break_status' => 'not_on_break'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully',
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check in',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminCheckOut(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $timestamp = Carbon::parse($request->get('timestamp', now()));

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                return response()->json([
                    'error' => 'Must check in before checking out'
                ], 400);
            }

            if ($attendance->break_status === 'on_break') {
                return response()->json([
                    'error' => 'Cannot check out while on break'
                ], 400);
            }

            $checkInTime = Carbon::parse($attendance->check_in_time);
            $workingMinutes = $timestamp->diffInMinutes($checkInTime) - ($attendance->total_break_time ?? 0);

            $attendance->update([
                'check_out_time' => $timestamp,
                'working_hours' => max(0, $workingMinutes)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully',
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check out',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminStartBreak(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $timestamp = Carbon::parse($request->get('timestamp', now()));

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                return response()->json([
                    'error' => 'Must check in before starting break'
                ], 400);
            }

            if ($attendance->break_status === 'on_break') {
                return response()->json([
                    'error' => 'Already on break'
                ], 400);
            }

            $attendance->update([
                'break_status' => 'on_break',
                'current_break_start' => $timestamp
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break started successfully',
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to start break',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminEndBreak(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $timestamp = Carbon::parse($request->get('timestamp', now()));

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if (!$attendance || $attendance->break_status !== 'on_break') {
                return response()->json([
                    'error' => 'Not currently on break'
                ], 400);
            }

            $breakStart = Carbon::parse($attendance->current_break_start);
            $breakDuration = $timestamp->diffInMinutes($breakStart);
            $totalBreakTime = ($attendance->total_break_time ?? 0) + $breakDuration;

            $attendance->update([
                'break_status' => 'not_on_break',
                'current_break_start' => null,
                'total_break_time' => $totalBreakTime
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break ended successfully',
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to end break',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getStatusLabel($status): string
    {
        return match($status) {
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'half_day' => 'Half Day',
            'on_break' => 'On Break',
            default => 'Unknown'
        };
    }

    private function determineStatus(Carbon $checkInTime): string
    {
        $workStartTime = Carbon::parse('09:00');
        
        if ($checkInTime->lte($workStartTime)) {
            return 'present';
        } elseif ($checkInTime->lte($workStartTime->copy()->addMinutes(15))) {
            return 'late';
        } else {
            return 'late';
        }
    }

    private function formatDuration(?int $minutes): string
    {
        if (!$minutes || $minutes <= 0) {
            return '0m';
        }
        
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
        }
        
        return "{$mins}m";
    }
}

