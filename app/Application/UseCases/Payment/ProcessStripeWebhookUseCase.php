<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

use App\Domain\Entities\Subscription;
use App\Domain\RepositoryInterfaces\SubscriptionRepositoryInterface;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use Illuminate\Support\Facades\Log;

final class ProcessStripeWebhookUseCase
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly TenantRepositoryInterface $tenantRepository
    ) {}

    public function execute(array $payload): void
    {
        $eventType = $payload['type'] ?? '';
        
        Log::info("Stripe webhook received: {$eventType}");

        match ($eventType) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($payload['data']['object']),
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($payload['data']['object']),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($payload['data']['object']),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload['data']['object']),
            default => Log::info("Unhandled Stripe event: {$eventType}")
        };
    }

    private function handleCheckoutSessionCompleted(array $session): void
    {
        $customerId = $session['customer'];
        $subscriptionId = $session['subscription'];
        $metadata = $session['metadata'] ?? [];
        $tenantSlug = $metadata['tenant_slug'] ?? null;
        $plan = $metadata['plan'] ?? 'basic';

        if (!$tenantSlug) {
            Log::error('Checkout session missing tenant_slug in metadata');
            return;
        }

        $tenant = $this->tenantRepository->findBySlug(\App\Domain\ValueObjects\TenantSlug::fromString($tenantSlug));
        
        if (!$tenant) {
            Log::error("Tenant not found for slug: {$tenantSlug}");
            return;
        }

        // Crear suscripción
        $subscription = Subscription::create(
            tenantId: $tenant->id(),
            stripeSubscriptionId: $subscriptionId,
            stripeCustomerId: $customerId,
            plan: $plan
        );

        $this->subscriptionRepository->save($subscription);

        // Activar tenant y marcar como suscrito
        $tenant->markAsSubscribed();
        $tenant->activate();
        $this->tenantRepository->save($tenant);

        Log::info("Subscription created for tenant: {$tenantSlug}");
    }

    private function handleInvoicePaymentSucceeded(array $invoice): void
    {
        $subscriptionId = $invoice['subscription'];
        
        $subscription = $this->subscriptionRepository->findByStripeSubscriptionId($subscriptionId);
        
        if (!$subscription) {
            Log::error("Subscription not found: {$subscriptionId}");
            return;
        }

        $subscription->activate();
        $subscription->updatePeriod(
            start: now()->toDateTimeImmutable(),
            end: now()->addMonth()->toDateTimeImmutable()
        );
        
        $this->subscriptionRepository->save($subscription);

        Log::info("Payment succeeded for subscription: {$subscriptionId}");
    }

    private function handleInvoicePaymentFailed(array $invoice): void
    {
        $subscriptionId = $invoice['subscription'];
        
        $subscription = $this->subscriptionRepository->findByStripeSubscriptionId($subscriptionId);
        
        if (!$subscription) {
            return;
        }

        $subscription->markAsPastDue();
        $this->subscriptionRepository->save($subscription);

        Log::warning("Payment failed for subscription: {$subscriptionId}");
    }

    private function handleSubscriptionDeleted(array $stripeSubscription): void
    {
        $subscriptionId = $stripeSubscription['id'];
        
        $subscription = $this->subscriptionRepository->findByStripeSubscriptionId($subscriptionId);
        
        if (!$subscription) {
            return;
        }

        $subscription->cancel();
        $this->subscriptionRepository->save($subscription);

        // Opcional: suspender tenant
        $tenant = $this->tenantRepository->findById($subscription->tenantId());
        if ($tenant) {
            $tenant->suspend();
            $this->tenantRepository->save($tenant);
        }

        Log::info("Subscription canceled: {$subscriptionId}");
    }
}
