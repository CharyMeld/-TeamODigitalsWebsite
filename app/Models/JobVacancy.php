<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    protected $fillable = [
        'title',
        'department',
        'location',
        'job_type',
        'description',
        'requirements',
        'responsibilities',
        'salary_range',
        'application_deadline',
        'status',
        'posted_by',
    ];

    protected $casts = [
        'application_deadline' => 'date',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('application_deadline', '>=', now());
    }
}
