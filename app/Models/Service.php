<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
        
        static::updating(function ($service) {
            if ($service->isDirty('title') && empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order services by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Get a shortened description for display purposes.
     */
    public function getShortDescriptionAttribute(): string
    {
        return Str::limit($this->description, 150);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('img/services/default-service.png');
        }

        // Handle both img/ and images/ paths
        $imagePath = $this->image;
        
        // If the path doesn't start with img/ or images/, assume it's in img/services/
        if (!str_starts_with($imagePath, 'img/') && !str_starts_with($imagePath, 'images/')) {
            $imagePath = 'img/services/' . $imagePath;
        }

        // Check if file exists in public directory
        if (file_exists(public_path($imagePath))) {
            return asset($imagePath);
        }
        
        // Return a default image if the service image doesn't exist
        return asset('img/services/default-service.png');
    }
}
