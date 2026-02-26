<?php

namespace App\Console\Commands;

use App\Mail\TrialExpiringMail;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTrialReminders extends Command
{
    protected $signature = 'tenants:send-trial-reminders';
    protected $description = 'Send email reminders to tenants with trials expiring soon';

    public function handle()
    {
        $this->info('Checking for trials expiring in 3 days...');

        // Trials expiring in exactly 3 days
        $tenants = Tenant::whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', now()->addDays(3))
            ->whereNull('subscribed_at')
            ->get();

        foreach ($tenants as $tenant) {
            if ($tenant->email) {
                Mail::to($tenant->email)->send(new TrialExpiringMail($tenant, 3));
                $this->info("Reminder sent to: {$tenant->email}");
            }
        }

        // Trials expiring in exactly 1 day
        $this->info('Checking for trials expiring in 1 day...');
        
        $tenants = Tenant::whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', now()->addDay())
            ->whereNull('subscribed_at')
            ->get();

        foreach ($tenants as $tenant) {
            if ($tenant->email) {
                Mail::to($tenant->email)->send(new TrialExpiringMail($tenant, 1));
                $this->info("Urgent reminder sent to: {$tenant->email}");
            }
        }

        $this->info('Done!');
    }
}
