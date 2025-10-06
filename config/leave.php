<?php
/*
|--------------------------------------------------------------------------
| Configuration File
|--------------------------------------------------------------------------
*/

// config/leave.php
return [
    'quotas' => [
        'Annual Leave' => 21,
        'Sick Leave' => 10,
        'Emergency Leave' => 3,
        'Maternity Leave' => 90,
        'Paternity Leave' => 10,
        'Study Leave' => 5,
        'Compassionate Leave' => 3,
        'Medical Leave' => 30,
    ],

    'notice_periods' => [
        'Annual Leave' => 3, // days
        'Study Leave' => 14, // days
    ],

    'approval_flow' => [
        'employee' => ['admin', 'superadmin'],
        'admin' => ['superadmin'],
    ],

    'file_settings' => [
        'max_size' => 5120, // KB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'storage_disk' => 'public',
        'storage_path' => 'leave_attachments',
    ],
];
