<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    //  Correct table name
    protected $table = 'attendances';

    //  Correct columns from your real table
    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'break_status',
        'current_break_start',
        'total_break_time',
        'working_hours',
        'working_sessions',
        'notes',
    ];

    //  Proper casting for easier usage
    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'current_break_start' => 'datetime',
        'total_break_time' => 'integer',  // minutes
        'working_hours' => 'integer',     // minutes
        'working_sessions' => 'array',    // JSON array
    ];

    //  Auto-append useful attributes when serialized
    protected $appends = [
        'is_on_break',
        'formatted_break_time',
        'formatted_working_hours',
        'status_label',
    ];

    /**
     * Relationship: Attendance belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if user is currently on break
     */
    public function getIsOnBreakAttribute()
    {
        return $this->break_status === 'on_break';
    }

    /**
     * Get formatted break time
     */
    public function getFormattedBreakTimeAttribute()
    {
        return $this->formatDuration($this->total_break_time);
    }

    /**
     * Get formatted working hours
     */
    public function getFormattedWorkingHoursAttribute()
    {
        return $this->formatDuration($this->working_hours);
    }

    /**
     * Get status label for UI display
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'half_day' => 'Half Day',
            'on_break' => 'On Break',
            default => 'Unknown',
        };
    }

    /**
     * Scope: Get today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope: Get attendance for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get attendance within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Helper: Format minutes to H:M string
     */
    private function formatDuration(?int $minutes): string
    {
        if (!$minutes || $minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
        }

        return "{$mins}m";
    }
}

