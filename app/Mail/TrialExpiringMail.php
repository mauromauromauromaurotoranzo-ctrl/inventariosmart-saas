<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public int $daysRemaining
    ) {}

    public function build()
    {
        return $this->subject('Tu prueba gratuita expira pronto')
            ->view('emails.trial-expiring')
            ->with([
                'tenantName' => $this->tenant->name,
                'daysRemaining' => $this->daysRemaining,
                'upgradeUrl' => route('landing.pricing'),
            ]);
    }
}
