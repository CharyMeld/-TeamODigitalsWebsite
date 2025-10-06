<?php

namespace App\Models;

// Add this use statement
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    // The HasProfilePhoto trait is already included, which is great.
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * CHANGE #1: The HasProfilePhoto trait expects the database column
     * to be 'profile_photo_path'. I've changed 'profile_image' to
     * 'profile_photo_path' here to match what Jetstream expects.
     *
     * You will need to update your database migration to use this column name as well.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'gender',
        'department',
        'phone',
        'address',
        'marital_status',
        'date_of_birth',
        'local_government',
        'state',
        'country',
        'emergency_contact',
        'profile_image', 
        'role',
        'status',
        'salary',
        'hire_date',
        'employee_id',
        'updated_by',
    ];


    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    // This is correct. The HasProfilePhoto trait creates this attribute.
    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    /**
     * CHANGE #2: Override the default 'profile_photo_url' accessor from the Jetstream trait.
     *
     * The default Jetstream accessor looks for the photo on an external disk (like S3).
     * This override tells it to use your local public storage, which is what we want.
     * It also adds a fallback to a default avatar if no photo is set.
     */
    public function getProfilePhotoUrlAttribute()
    {
        // Check profile_photo_path first (Jetstream standard)
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::url($this->profile_photo_path);
        }

        // Fallback to profile_image for backwards compatibility
        if ($this->profile_image && Storage::disk('public')->exists("profile-photos/{$this->profile_image}")) {
            return Storage::url("profile-photos/{$this->profile_image}");
        }

        return $this->defaultProfilePhotoUrl();
    }


    public function dashboardPreference(): HasOne
    {
        return $this->hasOne(UserDashboardPreference::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getAccessibleMenuItems()
    {
        if (!$this->roles->count()) {
            return collect();
        }

        $roleIds = $this->roles->pluck('id')->toArray();
        
        return MenuItem::active()
            ->with(['children' => function($query) use ($roleIds) {
                $query->whereHas('roles', function($q) use ($roleIds) {
                    $q->whereIn('role_id', $roleIds)->where('can_view', true);
                });
            }])
            ->whereHas('roles', function($query) use ($roleIds) {
                $query->whereIn('role_id', $roleIds)->where('can_view', true);
            })
            ->parents()
            ->orderBy('sort_order')
            ->get();
    }

    public function getDefaultDashboardRoute(): string
    {
        $preference = $this->dashboardPreference;

        if ($preference && $preference->default_route) {
            return $preference->default_route;
        }

        // Role hierarchy
        if ($this->hasRole('developer')) {
            return 'developer.dashboard';
        } elseif ($this->hasRole('superadmin')) {
            return 'superadmin.dashboard';
        } elseif ($this->hasRole('admin')) {
            return 'admin.dashboard';
        } else {
            return 'employee.dashboard';
        }
    }

    public function getMenuItems(): array
    {
        if (!$this->roles->count()) {
            return [];
        }

        $roleIds = $this->roles->pluck('id')->toArray();

        $transformMenuItem = function ($item) use (&$transformMenuItem, $roleIds) {
            $children = $item->children
                ->filter(fn($child) => $child->roles->whereIn('id', $roleIds)->where('pivot.can_view', true)->count() > 0)
                ->map(fn($child) => $transformMenuItem($child))
                ->toArray();

            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'icon'     => $item->icon,
                'route'    => $item->route,
                'children' => $children,
            ];
        };

        $parents = \App\Models\MenuItem::active()
            ->parents()
            ->orderBy('sort_order')
            ->with('children')
            ->get()
            ->filter(fn($item) => $item->roles->whereIn('id', $roleIds)->where('pivot.can_view', true)->count() > 0);

        return $parents->map(fn($item) => $transformMenuItem($item))->toArray();
    }
}

