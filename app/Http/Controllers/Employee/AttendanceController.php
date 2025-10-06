<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    // POST /employee/attendance/sign-in
    public function signIn(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->check_in_time) {
            return $this->renderDashboard('error', 'You have already checked in today.');
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $userId,
                'date' => $today,
                'check_in_time' => Carbon::now(),
                'status' => 'present', // ENUM-safe
                'break_status' => 'none',
                'total_break_time' => 0,
                'working_sessions' => 1,
            ]);
        } else {
            $attendance->update([
                'check_in_time' => Carbon::now(),
                'status' => 'present', // ENUM-safe
                'working_sessions' => $attendance->working_sessions + 1,
            ]);
        }

        Log::info('Check-in successful', [
            'user_id' => $userId,
            'attendance_id' => $attendance->id,
            'check_in_time' => $attendance->check_in_time
        ]);

        return $this->renderDashboard('success', 'Checked in successfully.');
    }

    // POST /employee/attendance/start-break
    public function startBreak(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return $this->renderDashboard('error', 'You must check in before starting a break.');
        }

        if ($attendance->check_out_time) {
            return $this->renderDashboard('error', 'You have already checked out today.');
        }

        if ($attendance->break_status === 'on_break') {
            return $this->renderDashboard('error', 'Break already started.');
        }

        $attendance->update([
            'break_status' => 'on_break',
            'current_break_start' => Carbon::now(),
        ]);

        Log::info('Break started', ['user_id' => $userId, 'attendance_id' => $attendance->id]);

        return $this->renderDashboard('success', 'Break started successfully.');
    }

    // POST /employee/attendance/end-break
    public function endBreak(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || $attendance->break_status !== 'on_break') {
            return $this->renderDashboard('error', 'No active break to end.');
        }

        $breakStart = Carbon::parse($attendance->current_break_start);
        $breakMinutes = $breakStart->diffInMinutes(Carbon::now());

        $attendance->update([
            'break_status' => 'none',
            'current_break_start' => null,
            'total_break_time' => $attendance->total_break_time + $breakMinutes,
        ]);

        Log::info('Break ended', ['user_id' => $userId, 'attendance_id' => $attendance->id]);

        return $this->renderDashboard('success', 'Break ended successfully.');
    }

    // POST /employee/attendance/sign-out
    public function signOut(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return $this->renderDashboard('error', 'You must check in before checking out.');
        }

        if ($attendance->check_out_time) {
            return $this->renderDashboard('error', 'You have already checked out today.');
        }

        // Auto-end any active break
        if ($attendance->break_status === 'on_break') {
            $breakStart = Carbon::parse($attendance->current_break_start);
            $breakMinutes = $breakStart->diffInMinutes(Carbon::now());

            $attendance->update([
                'break_status' => 'none',
                'current_break_start' => null,
                'total_break_time' => $attendance->total_break_time + $breakMinutes,
            ]);
            $attendance->refresh();
        }

        $now = Carbon::now();
        $checkIn = Carbon::parse($attendance->check_in_time);
        $workedMinutes = $checkIn->diffInMinutes($now);

        // Deduct total break time
        $workedMinutes = max(0, $workedMinutes - $attendance->total_break_time);

        $attendance->update([
            'check_out_time' => $now,
            'working_hours' => round($workedMinutes / 60, 2),
            'status' => 'present', // ENUM-safe
        ]);

        Log::info('Check-out successful', [
            'user_id' => $userId,
            'attendance_id' => $attendance->id,
            'working_hours' => $attendance->working_hours
        ]);

        return $this->renderDashboard('success', 'Checked out successfully.');
    }

    // Helper method to render dashboard
    private function renderDashboard($flashType = null, $flashMessage = null)
    {
        $user = Auth::user();
        $userId = $user->id;
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        $history = Attendance::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $flashData = [];
        if ($flashType && $flashMessage) {
            $flashData[$flashType] = $flashMessage;
        }

        return Inertia::render('Employee/Dashboard', [
            'user' => $user,
            'attendance' => $attendance,
            'history' => $history,
            'flash' => $flashData,
        ]);
    }
}

