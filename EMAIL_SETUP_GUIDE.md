# Email Configuration Guide for Password Reset

## Current Status
✅ Password reset routes are working
✅ Password reset pages are created
❌ Email not configured yet - needs your Gmail credentials

---

## Option 1: Gmail SMTP (Recommended for Production)

### Step 1: Create Gmail App Password

**IMPORTANT**: You cannot use your regular Gmail password. You MUST create an "App Password".

1. **Go to your Google Account**: https://myaccount.google.com/
2. **Enable 2-Step Verification** (if not already enabled):
   - Go to Security → 2-Step Verification
   - Follow the setup process
   
3. **Create App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - OR: Security → 2-Step Verification → App passwords (at bottom)
   - Select app: "Mail"
   - Select device: "Other (Custom name)"
   - Enter name: "Teamo Digital Solutions"
   - Click "Generate"
   - **Copy the 16-character password** (spaces don't matter)

### Step 2: Update .env File

Open `.env` file and update these lines:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-actual-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-actual-email@gmail.com"
MAIL_FROM_NAME="Teamo Digital Solutions"
```

**Replace**:
- `your-actual-email@gmail.com` with your Gmail address
- `your-16-character-app-password` with the app password from Step 1

### Step 3: Clear Cache and Test

```bash
php artisan config:clear
php artisan cache:clear
```

Then test by requesting a password reset at: http://localhost:8080/forgot-password

---

## Option 2: Mailtrap (Recommended for Development/Testing)

Mailtrap catches all emails so they don't actually get sent - perfect for testing!

### Step 1: Sign Up for Mailtrap

1. Go to: https://mailtrap.io/
2. Sign up for free account
3. Verify your email

### Step 2: Get SMTP Credentials

1. In Mailtrap dashboard, go to "Email Testing" → "Inboxes"
2. Click on "My Inbox" (or create a new inbox)
3. Go to "SMTP Settings" tab
4. Select "Laravel 9+" from integrations dropdown
5. Copy the credentials shown

### Step 3: Update .env File

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@teamodigital.com"
MAIL_FROM_NAME="Teamo Digital Solutions"
```

### Step 4: Test

1. Request password reset at: http://localhost:8080/forgot-password
2. Check your Mailtrap inbox to see the email
3. Click the reset link in the email

---

## Option 3: Keep Using Logs (Development Only)

If you want to keep emails in logs for now:

```env
MAIL_MAILER=log
```

Then check logs for reset links:
```bash
tail -100 storage/logs/laravel.log | grep "reset-password"
```

---

## Troubleshooting

### "Failed to authenticate" Error
- Check your app password is correct
- Make sure 2-Step Verification is enabled
- Try regenerating the app password

### "Connection refused" Error
- Check MAIL_PORT is 587 (not 465 or 25)
- Check MAIL_ENCRYPTION is "tls" (not "ssl")

### Emails not arriving
- Check spam folder
- Wait a few minutes (Gmail can be slow)
- Check Laravel logs: `tail -50 storage/logs/laravel.log`

---

## Testing the Setup

1. Go to: http://localhost:8080/forgot-password
2. Enter email: your-email@gmail.com
3. Click "Email Password Reset Link"
4. Check email inbox (or Mailtrap)
5. Click the link in email
6. Set new password
7. Login with new password

---

## Production Configuration (Later)

For production, consider using:
- **SendGrid**: https://sendgrid.com/ (12,000 free emails/month)
- **Amazon SES**: https://aws.amazon.com/ses/ (Very cheap)
- **Mailgun**: https://www.mailgun.com/ (5,000 free emails/month)
- **Postmark**: https://postmarkapp.com/ (Great for transactional emails)

