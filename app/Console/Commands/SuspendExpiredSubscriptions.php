<?php

namespace App\Console\Commands;

use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Illuminate\Console\Command;

class SuspendExpiredSubscriptions extends Command
{
    protected $signature = 'tenants:suspend-expired';
    
    protected $description = 'Suspender tenants con suscripción vencida o trial expirado sin pago';

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Buscando tenants con suscripciones expiradas...');

        // Buscar tenants con trial expirado y sin suscripción
        $expiredTrials = $this->tenantRepository->findExpiredTrials();
        
        $count = 0;
        foreach ($expiredTrials as $tenant) {
            if ($tenant->isActive()) {
                $tenant->suspend();
                $this->tenantRepository->save($tenant);
                
                $this->warn("Suspendido: {$tenant->name()} ({$tenant->slug()->value()})");
                $count++;
                
                // TODO: Enviar email de notificación al tenant
            }
        }

        $this->info("Total suspendidos: {$count}");
        
        return self::SUCCESS;
    }
}
