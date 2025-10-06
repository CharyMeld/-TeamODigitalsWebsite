<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_name',
        'employee_id',
        'department',
        'job_title',
        'contact',
        'leave_type',
        'start_date',
        'end_date',
        'number_of_days',
        'reason',
        'superadmin',
        'status',
        'comments',
        'attachment',
         'employee_acknowledgement' => 'boolean',
    ];
}

