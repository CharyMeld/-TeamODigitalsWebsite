<?php

namespace App\Http\Controllers\Admin;

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * Display a listing of blogs
     */
    public function index(Request $request)
    {
        $query = Blog::with('author:id,name,email')
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('primary_keyword', 'LIKE', "%{$search}%")
                  ->orWhere('meta_description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $blogs = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Blogs/Index', [
            'blogs' => $blogs,
            'filters' => $request->only(['search', 'status', 'category'])
        ]);
    }

    /**
     * Show the form for creating a new blog
     */
    public function create()
    {
        return Inertia::render('Admin/Blogs/Create');
    }

    /**
     * Store a newly created blog
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'meta_description' => 'required|string|max:160',
            'primary_keyword' => 'required|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'tags' => 'nullable|string',
            'introduction' => 'required|string',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string',
            'sections.*.content' => 'required|string',
            'sections.*.images' => 'nullable|array',
            'sections.*.alt_text' => 'nullable|string',
            'sections.*.subsections' => 'nullable|array',
            'conclusion' => 'required|string',
            'cta_text' => 'nullable|string',
            'cta_link' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'featured_image_alt' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
            'canonical_url' => 'nullable|url',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Blog::generateSlug($validated['title']);
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')
                ->store('blogs/featured', 'public');
        }

        // Handle section images
        if (isset($validated['sections'])) {
            foreach ($validated['sections'] as $index => &$section) {
                if ($request->hasFile("sections.{$index}.images")) {
                    $uploadedImages = [];
                    foreach ($request->file("sections.{$index}.images") as $image) {
                        $uploadedImages[] = $image->store('blogs/sections', 'public');
                    }
                    $section['images'] = $uploadedImages;
                }
            }
        }

        // Set author
        $validated['author_id'] = Auth::id();

        // Set published_at if status is published
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Create blog
        $blog = Blog::create($validated);

        // Calculate reading time
        $blog->reading_time = $blog->calculateReadingTime();

        // Generate schema markup
        $blog->schema_markup = $blog->generateSchemaMarkup();

        $blog->save();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully!');
    }

    /**
     * Display the specified blog
     */
    public function show(Blog $blog)
    {
        $blog->load('author:id,name,email');

        return Inertia::render('Admin/Blogs/Show', [
            'blog' => $blog
        ]);
    }

    /**
     * Show the form for editing the specified blog
     */
    public function edit(Blog $blog)
    {
        $blog->load('author:id,name,email');

        return Inertia::render('Admin/Blogs/Edit', [
            'blog' => $blog
        ]);
    }

    /**
     * Update the specified blog
     */
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'meta_description' => 'required|string|max:160',
            'primary_keyword' => 'required|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'tags' => 'nullable|string',
            'introduction' => 'required|string',
            'sections' => 'nullable|array',
            'conclusion' => 'required|string',
            'cta_text' => 'nullable|string',
            'cta_link' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'featured_image_alt' => 'nullable|string',
            'category' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'boolean',
            'canonical_url' => 'nullable|url',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')
                ->store('blogs/featured', 'public');
        }

        // Handle section images
        if (isset($validated['sections'])) {
            foreach ($validated['sections'] as $index => &$section) {
                if ($request->hasFile("sections.{$index}.images")) {
                    $uploadedImages = [];
                    foreach ($request->file("sections.{$index}.images") as $image) {
                        $uploadedImages[] = $image->store('blogs/sections', 'public');
                    }
                    $section['images'] = $uploadedImages;
                }
            }
        }

        // Set published_at if status changed to published
        if ($validated['status'] === 'published' && $blog->status !== 'published') {
            $validated['published_at'] = now();
        }

        // Update blog
        $blog->update($validated);

        // Recalculate reading time
        $blog->reading_time = $blog->calculateReadingTime();

        // Regenerate schema markup
        $blog->schema_markup = $blog->generateSchemaMarkup();

        $blog->save();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully!');
    }

    /**
     * Remove the specified blog
     */
    public function destroy(Blog $blog)
    {
        // Delete associated images
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        if ($blog->sections) {
            foreach ($blog->sections as $section) {
                if (isset($section['images'])) {
                    foreach ($section['images'] as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully!');
    }
}
