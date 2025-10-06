<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'thumbnail',
        'category',
        'status',
        'featured',
        'meta_title',
        'meta_description',
        'author_id',
        'published_at',
        'likes',
        'views',
        // SEO fields
        'primary_keyword',
        'secondary_keywords',
        'introduction',
        'sections',
        'conclusion',
        'cta_text',
        'cta_link',
        'featured_image',
        'featured_image_alt',
        'tags',
        'canonical_url',
        'schema_markup',
        'reading_time'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'likes' => 'integer',
        'views' => 'integer',
        'sections' => 'array',
        'reading_time' => 'integer'
    ];

    protected $dates = [
        'deleted_at',
        'published_at'
    ];

    /**
     * Scope for published blogs only
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for featured blogs
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the author of the blog
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get blog likes (if using user tracking)
     */
    public function blogLikes()
    {
        return $this->hasMany(BlogLike::class);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug'; // Use slug instead of id for SEO-friendly URLs
    }

    /**
     * Generate excerpt if not provided
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return \Illuminate\Support\Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get formatted category name
     */
    public function getCategoryNameAttribute()
    {
        return ucfirst(str_replace('-', ' ', $this->category));
    }

    /**
     * Calculate and update reading time
     */
    public function calculateReadingTime()
    {
        $wordCount = 0;

        // Count words in introduction
        if ($this->introduction) {
            $wordCount += str_word_count(strip_tags($this->introduction));
        }

        // Count words in sections
        if ($this->sections) {
            foreach ($this->sections as $section) {
                $wordCount += str_word_count(strip_tags($section['content'] ?? ''));
                if (isset($section['subsections'])) {
                    foreach ($section['subsections'] as $subsection) {
                        $wordCount += str_word_count(strip_tags($subsection['content'] ?? ''));
                    }
                }
            }
        }

        // Count words in conclusion
        if ($this->conclusion) {
            $wordCount += str_word_count(strip_tags($this->conclusion));
        }

        // Average 200 words per minute
        return ceil($wordCount / 200);
    }

    /**
     * Generate schema markup for SEO
     */
    public function generateSchemaMarkup()
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->title,
            'description' => $this->meta_description ?? $this->excerpt,
            'image' => $this->featured_image ? asset('storage/' . $this->featured_image) : null,
            'author' => [
                '@type' => 'Person',
                'name' => $this->author->name ?? 'Teamo Digital Solutions'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Teamo Digital Solutions',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png')
                ]
            ],
            'datePublished' => $this->published_at?->toIso8601String(),
            'dateModified' => $this->updated_at->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $this->slug)
            ]
        ]);
    }

    /**
     * Auto-generate slug from title
     */
    public static function generateSlug($title)
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
