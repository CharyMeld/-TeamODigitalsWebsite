<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'route',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'description',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Scope to get only active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only parent menu items.
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get all descendants of this menu item.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Get roles that have access to this menu item.
     */
    public function roles()
    {
        return $this->belongsToMany(
            \Spatie\Permission\Models\Role::class,
            'role_menu_permissions',
            'menu_item_id',
            'role_id'
        )->withPivot('can_view');
    }

    /**
     * Check if a role has access to this menu item.
     */
    public function hasRoleAccess(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Get full route path for this menu item.
     */
    public function getFullRoute(): ?string
    {
        if ($this->route) {
            return $this->route;
        }

        // Auto-generate route if not set
        return $this->generateRoute();
    }

    /**
     * Auto-generate route based on menu hierarchy.
     */
    private function generateRoute(): ?string
    {
        $slug = $this->slug ?: \Illuminate\Support\Str::slug($this->name);

        if ($this->parent_id) {
            $parent = $this->parent;
            if ($parent) {
                $parentSlug = $parent->slug ?: \Illuminate\Support\Str::slug($parent->name);
                return "{$parentSlug}.{$slug}";
            }
        }

        return $slug;
    }
}
