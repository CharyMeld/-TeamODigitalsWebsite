<?php
// Model: RoleMenuPermission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleMenuPermission extends Model
{
    protected $fillable = [
        'role_id',
        'menu_item_id',
        'can_view'
    ];

    protected $casts = [
        'can_view' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
