<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function build()
    {
        return $this->subject('¡Bienvenido a InventarioSmart!')
            ->view('emails.tenant-welcome')
            ->with([
                'tenantName' => $this->tenant->name,
                'tenantUrl' => $this->tenant->getUrl(),
                'trialEndsAt' => $this->tenant->trial_ends_at?->format('d/m/Y'),
            ]);
    }
}
