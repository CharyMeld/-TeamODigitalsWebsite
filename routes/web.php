<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\{
    HomeController,
    AboutController,
    ContactController,
    BlogController,
    GalleryController,
    CareersController,
    Auth\AuthenticatedSessionController,
    ServicesController,
    NewsletterController,
    SitemapController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Debug authentication info (only for logged-in users)
Route::get('/debug-auth', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    return response()->json([
        'user_id' => $user->id,
        'email' => $user->email,
        'is_authenticated' => Auth::check(),
        'roles' => $user->roles->pluck('name'),
        'has_admin_role' => $user->hasRole('admin'),
        'has_developer_role' => $user->hasRole('developer'),
        'has_superadmin_role' => $user->hasRole('superadmin'),
    ]);
})->middleware('auth');

// Test menu system
Route::get('/test-menu', function () {
    $menuService = app(\App\Services\MenuService::class);

    if (!Auth::check()) {
        return response()->json([
            'error' => 'Not logged in',
            'message' => 'Please login first'
        ]);
    }

    $user = Auth::user();
    $roles = $user->roles->pluck('name')->toArray();

    if (empty($roles) && $user->role) {
        $roles = [$user->role];
    }

    $menus = $menuService->getMenuForRole($roles);

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_column' => $user->role,
        ],
        'roles' => $roles,
        'menu_count' => count($menus),
        'menus' => $menus,
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');

// SEO Routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Services
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/services/{service}', [ServicesController::class, 'show'])->name('services.show');

// About
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact-submit', [ContactController::class, 'submit'])->name('contact.submit');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{id}/like', [BlogController::class, 'like'])->name('blog.like');

// Temporary migration route (remove after use)
Route::get('/run-blog-migration', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_10_04_100000_add_seo_fields_to_blogs_table.php',
            '--force' => true
        ]);
        return 'Migration completed successfully! You can now create blog posts.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
})->middleware('auth');

// Blog seeder route
Route::get('/seed-blogs', function() {
    try {
        // First, run the SEO migration
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_10_04_100000_add_seo_fields_to_blogs_table.php',
            '--force' => true
        ]);

        // Then seed the blogs
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'BlogSeeder',
            '--force' => true
        ]);

        $blogCount = \App\Models\Blog::count();

        return response()->json([
            'success' => true,
            'message' => 'Successfully created ' . $blogCount . ' SEO-optimized blog posts!',
            'total_blogs' => $blogCount,
            'view_blogs' => url('/blog'),
            'admin_manage' => url('/superadmin/blogs'),
            'homepage' => url('/#blog-section')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth');

// Test email route with custom recipient
Route::get('/test-email/{email?}', function($email = 'charlesikesh@gmail.com') {
    try {
        $config = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_FROM' => config('mail.from.address'),
        ];

        \Log::info('Email test - Configuration:', $config);

        // Use Swift Mailer events to log SMTP communication
        $swiftMailer = app('mailer')->getSwiftMailer();
        $logger = new \Swift_Plugins_Loggers_ArrayLogger();
        $swiftMailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

        \Illuminate\Support\Facades\Mail::raw('This is a test email from Teamo Digital Solutions Analytics System.

If you received this email, SMTP is working correctly!

Sent at: ' . now()->toDateTimeString() . '
To: ' . $email, function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email - Analytics System [' . now()->format('H:i:s') . ']')
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });

        $smtpLog = $logger->dump();
        \Log::info('Email test - SMTP Log:', ['smtp_log' => $smtpLog]);
        \Log::info('Email test - Email sent successfully to: ' . $email);

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully to ' . $email,
            'config' => $config,
            'smtp_log' => $smtpLog,
            'tip' => 'Check spam folder and try searching in Gmail for: from:teamodigital1@gmail.com'
        ]);
    } catch (\Exception $e) {
        \Log::error('Email test failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('auth');

// Newsletter (only keep controller version)
Route::post('/newsletter/subscribe', [BlogController::class, 'subscribe'])
    ->name('newsletter.subscribe');

// Gallery
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('storage/gallery/{filename}', function ($filename) {
    $path = storage_path('gallery/' . $filename);
    abort_unless(file_exists($path), 404);
    return response()->file($path);
})->where('filename', '.*\.(jpg|jpeg|png|gif|webp)$');

// Careers
Route::get('/careers', [CareersController::class, 'index'])->name('careers.index');
Route::get('/careers/{id}', [CareersController::class, 'show'])->name('careers.show');
Route::get('/careers/{id}/apply', [\App\Http\Controllers\JobApplicationController::class, 'apply'])->name('careers.apply');
Route::post('/careers/{id}/apply', [\App\Http\Controllers\JobApplicationController::class, 'submit'])->name('careers.submit');

// Sitemap & robots
Route::get('/sitemap.xml', fn() => response(view('sitemap.xml'))
    ->header('Content-Type', 'application/xml'))->name('sitemap');

Route::get('/robots.txt', fn() => response(view('robots.txt'))
    ->header('Content-Type', 'text/plain'))->name('robots');

// Legacy redirects
Route::redirect('/contact.php', '/contact', 301);
Route::redirect('/services.php', '/services', 301);

/*
|--------------------------------------------------------------------------
| Auth Routes (Inertia SPA)
|--------------------------------------------------------------------------
*/




Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Password Reset Routes
Route::get('/forgot-password', function () {
    return \Inertia\Inertia::render('Auth/ForgotPassword', [
        'status' => session('status')
    ]);
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = \Illuminate\Support\Facades\Password::sendResetLink(
        $request->only('email')
    );

    return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return \Inertia\Inertia::render('Auth/ResetPassword', [
        'token' => $token,
        'email' => request('email')
    ]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = \Illuminate\Support\Facades\Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => \Illuminate\Support\Facades\Hash::make($password)
            ])->save();
        }
    );

    return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');
