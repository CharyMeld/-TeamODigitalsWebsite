<?php

/**
|--------------------------------------------------------------------------
| Validation Rules
|--------------------------------------------------------------------------
*/

// app/Http/Requests/LeaveRequestValidation.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\LeaveRequestService;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'leave_type' => 'required|string|in:Annual Leave,Sick Leave,Emergency Leave,Maternity Leave,Paternity Leave,Study Leave,Compassionate Leave,Medical Leave',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            $startDate = $this->start_date;
            $endDate = $this->end_date;
            $leaveType = $this->leave_type;

            // Calculate days
            $requestedDays = LeaveRequestService::calculateBusinessDays($startDate, $endDate);

            // Check quota
            if (!LeaveRequestService::checkLeaveQuota($user->id, $leaveType, $requestedDays)) {
                $validator->errors()->add('leave_type', 'You have exceeded your quota for this leave type.');
            }

            // Check overlapping requests
            if (LeaveRequestService::hasOverlappingRequests($user->id, $startDate, $endDate)) {
                $validator->errors()->add('start_date', 'You already have a leave request for overlapping dates.');
            }

            // Check minimum notice period (e.g., 3 days for annual leave)
            if ($leaveType === 'Annual Leave') {
                $daysDifference = now()->diffInDays($startDate);
                if ($daysDifference < 3) {
                    $validator->errors()->add('start_date', 'Annual leave requires at least 3 days notice.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'leave_type.in' => 'Please select a valid leave type.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'reason.min' => 'Please provide a detailed reason (minimum 10 characters).',
            'attachment.mimes' => 'Attachment must be a PDF, DOC, DOCX, or image file.',
            'attachment.max' => 'Attachment size cannot exceed 5MB.'
        ];
    }
}

