# Quick Start Guide - Enhanced Login System

## 🚀 Getting Started (5 minutes)

### Step 1: Run the Migration
```bash
php artisan migrate
```
This creates the `login_activities` table for tracking logins.

### Step 2: Test the Enhanced Login
1. Visit `/login` in your browser
2. Try the new features:
   - **Password toggle**: Click the eye icon to show/hide password
   - **Rate limiting**: Make 3-4 failed attempts to see the warning
   - **Better errors**: See improved error messages

### Step 3: Setup Social Login (Optional)

#### For Google:
1. Add to `.env`:
```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_secret_here
```

2. Test: Click "Google" button on login page

#### For GitHub:
1. Add to `.env`:
```env
GITHUB_CLIENT_ID=your_client_id_here
GITHUB_CLIENT_SECRET=your_secret_here
```

2. Test: Click "GitHub" button on login page

---

## 📊 View Login Activity

### In Tinker:
```bash
php artisan tinker
```

```php
// Get all login activities
LoginActivity::all();

// Get recent successful logins
LoginActivity::successful()->recent(7)->get();

// Get failed login attempts
LoginActivity::failed()->get();

// Get activity for specific user
LoginActivity::forUser(1)->get();
```

### In Your Code:
```php
// In any controller
use App\Models\LoginActivity;

// Get user's login history
$activities = LoginActivity::where('user_id', auth()->id())
    ->orderBy('attempted_at', 'desc')
    ->paginate(20);
```

---

## 🔑 What Changed?

### For Users:
- ✅ Password show/hide button
- ✅ Clear warnings when approaching lockout
- ✅ Login with Google or GitHub
- ✅ Better error messages

### For Developers:
- ✅ Complete login activity tracking
- ✅ Better token management (one per device)
- ✅ Role-based redirects work correctly
- ✅ Account status checks before login
- ✅ OAuth integration ready to use

---

## 🎯 Common Tasks

### 1. View Failed Login Attempts
```php
$failed = LoginActivity::failed()
    ->where('email', 'user@example.com')
    ->recent(30)
    ->get();
```

### 2. Check Last Login
```php
$lastLogin = LoginActivity::successful()
    ->forUser($userId)
    ->latest('attempted_at')
    ->first();

echo "Last login: " . $lastLogin->attempted_at;
echo "From: " . $lastLogin->ip_address;
echo "Device: " . $lastLogin->device_type;
```

### 3. Security Alert for New IP
```php
$user = auth()->user();
$currentIp = request()->ip();

$previousIps = LoginActivity::successful()
    ->forUser($user->id)
    ->pluck('ip_address')
    ->unique();

if (!$previousIps->contains($currentIp)) {
    // Send alert: "Login from new device/location"
    Mail::to($user)->send(new NewDeviceLogin($currentIp));
}
```

---

## 🛠️ Customization

### Change Rate Limit
Edit `app/Http/Requests/Auth/LoginRequest.php`:
```php
// Line 62-63
$maxAttempts = 5;      // Change to 3 for stricter
$decayMinutes = 1;     // Change to 5 for longer lockout
```

### Change Default Role for OAuth Users
Edit `app/Http/Controllers/Auth/SocialLoginController.php`:
```php
// Line 87
'role' => 'employee',  // Change to 'user' or other default role
```

### Disable Social Login Buttons
Edit `resources/js/Pages/Auth/Login.vue`:
```vue
<!-- Comment out lines 160-198 to hide social login buttons -->
```

---

## 📱 Social Login Setup Guides

### Google OAuth Setup (Detailed)

1. **Go to Google Cloud Console**
   - https://console.cloud.google.com/

2. **Create Project**
   - New Project → Enter name → Create

3. **Enable APIs**
   - APIs & Services → Library
   - Search "Google+ API" → Enable

4. **Create Credentials**
   - APIs & Services → Credentials
   - Create Credentials → OAuth 2.0 Client ID
   - Application type: Web application
   - Name: "Your App Login"

5. **Add Redirect URIs**
   ```
   http://localhost/auth/google/callback
   https://yourdomain.com/auth/google/callback
   ```

6. **Copy Credentials**
   - Client ID → Copy to `.env` as `GOOGLE_CLIENT_ID`
   - Client Secret → Copy to `.env` as `GOOGLE_CLIENT_SECRET`

7. **Test**
   - Visit `/login`
   - Click "Google" button
   - Should redirect to Google login

### GitHub OAuth Setup (Detailed)

1. **Go to GitHub Settings**
   - https://github.com/settings/developers

2. **New OAuth App**
   - Application name: "Your App"
   - Homepage URL: `http://localhost`
   - Callback URL: `http://localhost/auth/github/callback`

3. **Copy Credentials**
   - Client ID → `.env` as `GITHUB_CLIENT_ID`
   - Generate new client secret → `.env` as `GITHUB_CLIENT_SECRET`

4. **Test**
   - Visit `/login`
   - Click "GitHub" button

---

## 🔍 Monitoring & Analytics

### Daily Failed Login Report
```php
// Run this daily via scheduler
$failed = LoginActivity::failed()
    ->where('attempted_at', '>=', now()->subDay())
    ->get()
    ->groupBy('email');

foreach ($failed as $email => $attempts) {
    if ($attempts->count() > 10) {
        // Alert: Possible attack on account
    }
}
```

### Most Active Users
```php
$topUsers = LoginActivity::successful()
    ->recent(30)
    ->get()
    ->groupBy('user_id')
    ->map(fn($logins) => $logins->count())
    ->sortDesc()
    ->take(10);
```

### Browser Statistics
```php
$browsers = LoginActivity::successful()
    ->recent(30)
    ->get()
    ->groupBy('browser')
    ->map(fn($logins) => $logins->count());

// Result: ['Chrome' => 145, 'Firefox' => 23, ...]
```

---

## ⚠️ Important Notes

1. **Migration Required**: Run `php artisan migrate` before using
2. **Socialite Package**: Install with `composer require laravel/socialite`
3. **OAuth Credentials**: Add to `.env`, never commit to git
4. **HTTPS in Production**: OAuth providers require HTTPS for production
5. **Storage Permissions**: Ensure `storage/app/public/profile-photos` is writable

---

## 🐛 Common Issues

### "Class LoginActivity not found"
→ Run migration: `php artisan migrate`

### Social login redirects to error page
→ Check `.env` credentials are correct
→ Verify callback URLs match in provider settings

### Password toggle not showing
→ Clear browser cache
→ Rebuild assets: `npm run build`

### Rate limit not working
→ Check session driver is working
→ Clear cache: `php artisan cache:clear`

---

## 📚 Further Reading

- **Full Documentation**: See `LOGIN_ENHANCEMENTS.md`
- **Socialite Docs**: https://laravel.com/docs/socialite
- **Laravel Security**: https://laravel.com/docs/security
- **Jetstream Docs**: https://jetstream.laravel.com/

---

**Need Help?** Check `LOGIN_ENHANCEMENTS.md` for detailed explanations.
