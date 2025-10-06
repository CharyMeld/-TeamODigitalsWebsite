<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'default_route',
        'sidebar_collapsed',
        'custom_settings'
    ];

    protected $casts = [
        'sidebar_collapsed' => json_encode(false),
        'custom_settings' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
