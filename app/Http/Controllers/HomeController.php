<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Blog;
use App\Models\Testimonial;
use App\Models\Client;
use App\Http\Controllers\ServicesController;

class HomeController extends Controller
{
    public function index()
    {
        // Initialize empty collections as fallback
        $testimonials = collect([]);
        $blogs = collect([]);

        try {
            // Try to fetch testimonials
            $testimonials = Testimonial::where('status', 'active')
                                       ->orderBy('sort_order', 'asc')
                                       ->get();
        } catch (\Exception $e) {
            \Log::info('Testimonials table not ready: ' . $e->getMessage());
        }

        try {
            // Try to fetch published blogs with author info
            $blogs = Blog::published()
                         ->with('author:id,name')
                         ->orderBy('published_at', 'desc')
                         ->take(6)
                         ->get();
        } catch (\Exception $e) {
            \Log::info('Blogs table not ready: ' . $e->getMessage());
        }

        // Get services from ServicesController
        $servicesController = new ServicesController();
        $servicesData = $servicesController->index();
        $services = $servicesData->getData()['services'];

        return view('home', compact('testimonials', 'blogs', 'services'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return redirect()->route('home');
        }

        // Redirect to blog search with query parameter
        return redirect()->route('blog', ['search' => $query]);
    }
}

