# Laravel Socialite Installation Instructions

## Install Laravel Socialite

Since artisan commands are not working due to PHP version mismatch, you need to install Laravel Socialite manually:

### Option 1: Update composer.json manually

Add this to your `composer.json` in the `require` section:

```json
"laravel/socialite": "^5.0"
```

Then run:
```bash
composer update laravel/socialite
```

### Option 2: Direct composer command (if available)

```bash
composer require laravel/socialite
```

## After Installation

Once Socialite is installed, the OAuth controller and routes are already created and ready to use.

## Setup Google OAuth

1. Go to https://console.cloud.google.com/
2. Create a new project or select existing
3. Enable Google+ API
4. Go to Credentials → Create Credentials → OAuth 2.0 Client ID
5. Add authorized redirect URIs:
   - http://localhost/auth/google/callback
   - Your production URL/auth/google/callback
6. Copy Client ID and Client Secret to your `.env` file

## Setup GitHub OAuth (Optional)

1. Go to https://github.com/settings/developers
2. New OAuth App
3. Set callback URL to: http://localhost/auth/github/callback
4. Copy Client ID and Client Secret to your `.env` file
