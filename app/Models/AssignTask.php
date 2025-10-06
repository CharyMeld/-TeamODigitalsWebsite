<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'employee_id',
        'task_title',
        'task_type',
        'task_priority',
        'deadline',
        'task_description',
        'work_location',
        'project_name',
        'attachment',
    ];
}

