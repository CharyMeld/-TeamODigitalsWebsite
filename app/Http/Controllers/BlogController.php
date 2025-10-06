<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    /**
     * Display a listing of blogs with search and filter functionality
     */
    public function index(Request $request)
    {
        $query = Blog::published()->with('author:id,name,email');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('introduction', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('conclusion', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('primary_keyword', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('meta_description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Order by latest and paginate
        $blogs = $query->orderBy('published_at', 'desc')
                      ->paginate(9) // 9 posts per page for a 3x3 grid
                      ->withQueryString(); // Preserve search/filter parameters in pagination

        return view('blog.index', compact('blogs'));
    }

    /**
     * Display a single blog post
     */
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
                    ->published()
                    ->with('author:id,name,email')
                    ->firstOrFail();

        // Increment view count
        $blog->increment('views');

        // Get related blogs (same category, excluding current)
        $relatedBlogs = Blog::published()
                           ->where('category', $blog->category)
                           ->where('id', '!=', $blog->id)
                           ->orderBy('published_at', 'desc')
                           ->limit(3)
                           ->get();

        return view('blog-detail', compact('blog', 'relatedBlogs'));
    }

    /**
     * Handle blog like functionality
     */
    public function like(Request $request, $id): JsonResponse
    {
        try {
            $blog = Blog::findOrFail($id);
            
            // Simple like increment (you might want to implement user-specific likes)
            $blog->increment('likes');
            
            return response()->json([
                'success' => true,
                'likes' => $blog->likes,
                'message' => 'Blog liked successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error liking blog post'
            ], 500);
        }
    }

    /**
     * Handle advanced like functionality with user tracking
     * Use this if you want to prevent duplicate likes per user
     */
    public function likeWithUserTracking(Request $request, $id): JsonResponse
    {
        try {
            $blog = Blog::findOrFail($id);
            $userId = auth()->id(); // Assumes user authentication
            $sessionId = session()->getId(); // For guest users
            
            // Check if user has already liked this blog
            $existingLike = \App\Models\BlogLike::where('blog_id', $id)
                ->where(function ($query) use ($userId, $sessionId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->first();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                $blog->decrement('likes');
                $isLiked = false;
            } else {
                // Like
                \App\Models\BlogLike::create([
                    'blog_id' => $id,
                    'user_id' => $userId,
                    'session_id' => $userId ? null : $sessionId,
                ]);
                $blog->increment('likes');
                $isLiked = true;
            }
            
            return response()->json([
                'success' => true,
                'likes' => $blog->likes,
                'is_liked' => $isLiked,
                'message' => $isLiked ? 'Blog liked successfully' : 'Blog unliked successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing like'
            ], 500);
        }
    }
}
