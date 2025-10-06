<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    echo "Testing email configuration...\n\n";
    echo "MAIL_MAILER: " . config('mail.default') . "\n";
    echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
    echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
    echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
    echo "MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "MAIL_FROM: " . config('mail.from.address') . "\n\n";

    echo "Sending test email...\n";
    
    Mail::raw('This is a test email from Teamo Digital Solutions', function ($message) {
        $message->to('charlesikesh@gmail.com')
                ->subject('Test Email - Analytics System')
                ->from(config('mail.from.address'), config('mail.from.name'));
    });

    echo "✅ Email sent successfully!\n";
    echo "Please check your inbox (and spam folder)\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
