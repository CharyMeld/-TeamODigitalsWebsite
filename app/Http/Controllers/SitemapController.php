<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Blog;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        // Use APP_URL from .env if set, otherwise use current host
        $baseUrl = config('app.url') ?: request()->getSchemeAndHttpHost();

        $urls = [
            [
                'loc' => $baseUrl . '/',
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ],
            [
                'loc' => $baseUrl . '/about',
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8'
            ],
            [
                'loc' => $baseUrl . '/services',
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.9'
            ],
            [
                'loc' => $baseUrl . '/contact',
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ],
            [
                'loc' => $baseUrl . '/gallery',
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
        ];

        // Add blog posts dynamically if they exist
        try {
            $blogs = Blog::where('status', 'published')->get();
            foreach ($blogs as $blog) {
                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . $blog->id,
                    'lastmod' => $blog->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6'
                ];
            }
        } catch (\Exception $e) {
            // Blog table might not exist, skip
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        // Use APP_URL from .env if set, otherwise use current host
        $baseUrl = config('app.url') ?: request()->getSchemeAndHttpHost();

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "\n";
        $content .= "Sitemap: " . $baseUrl . "/sitemap.xml\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
