<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_vacancy_id',
        'full_name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'linkedin_url',
        'portfolio_url',
        'years_of_experience',
        'status',
        'admin_notes',
    ];

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }
}
