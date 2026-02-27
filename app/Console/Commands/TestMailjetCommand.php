<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailjetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mailjet {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Mailjet email configuration and send a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Testing Mailjet Configuration...');
        $this->newLine();

        // Display configuration
        $this->info('=== Mail Configuration ===');
        $this->line('Default Mailer: ' . config('mail.default'));
        $this->line('From Address: ' . config('mail.from.address'));
        $this->line('From Name: ' . config('mail.from.name'));
        $this->line('Mailjet API Key: ' . (config('services.mailjet.key') ? 'Set (***' . substr(config('services.mailjet.key'), -4) . ')' : 'NOT SET'));
        $this->line('Mailjet API Secret: ' . (config('services.mailjet.secret') ? 'Set (***' . substr(config('services.mailjet.secret'), -4) . ')' : 'NOT SET'));
        $this->line('Environment: ' . config('app.env'));
        $this->newLine();

        // Check if Mailjet is configured
        if (empty(config('services.mailjet.key')) || empty(config('services.mailjet.secret'))) {
            $this->error('❌ Mailjet API credentials are not configured!');
            $this->warn('Please set MAILJET_APIKEY and MAILJET_APISECRET in your .env file');
            return 1;
        }

        $this->info('✓ Mailjet credentials are configured');
        $this->newLine();

        // Attempt to send test email
        $this->info("Attempting to send test email to: {$email}");
        
        try {
            Log::info('Test email attempt via command', [
                'to' => $email,
                'command' => 'test:mailjet',
            ]);

            Mail::raw('This is a test email from Xelnova to verify Mailjet configuration.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Xelnova - Mailjet Test Email');
            });

            $this->newLine();
            $this->info('✓ Email sent successfully!');
            $this->line('Check the recipient inbox and also check logs for detailed information.');
            
            Log::info('Test email sent successfully via command', [
                'to' => $email,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send email!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->line('Error Class: ' . get_class($e));
            $this->line('Error Code: ' . $e->getCode());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            
            Log::error('Test email failed via command', [
                'to' => $email,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            $this->newLine();
            $this->warn('Check storage/logs/laravel.log for detailed error information');

            return 1;
        }
    }
}
