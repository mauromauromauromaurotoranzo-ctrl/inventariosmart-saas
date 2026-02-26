<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

use App\Domain\Entities\Payment;
use App\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantId;

final class HandleStripeWebhookUseCase
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(HandleStripeWebhookRequest $request): HandleStripeWebhookResponse
    {
        try {
            match ($request->eventType) {
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($request->payload),
                'invoice.paid' => $this->handleInvoicePaid($request->payload),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($request->payload),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($request->payload),
                default => null,
            };

            return HandleStripeWebhookResponse::success();

        } catch (\Exception $e) {
            return HandleStripeWebhookResponse::error($e->getMessage());
        }
    }

    private function handleCheckoutSessionCompleted(array $payload): void
    {
        $session = $payload['data']['object'] ?? [];
        $metadata = $session['metadata'] ?? [];
        
        $tenantId = TenantId::fromString($metadata['tenant_id'] ?? '');
        $plan = $metadata['plan'] ?? 'starter';
        
        $tenant = $this->tenantRepository->findById($tenantId);
        if (!$tenant) {
            throw new \RuntimeException('Tenant no encontrado: ' . $tenantId->value());
        }

        // Activar suscripción del tenant
        $tenant->markAsSubscribed();
        $tenant->updatePlan($plan);
        $this->tenantRepository->save($tenant);
    }

    private function handleInvoicePaid(array $payload): void
    {
        $invoice = $payload['data']['object'] ?? [];
        $subscription = $invoice['subscription'] ?? [];
        $metadata = $subscription['metadata'] ?? [];
        
        $tenantId = TenantId::fromString($metadata['tenant_id'] ?? '');
        
        // Crear registro de pago
        $payment = Payment::create(
            tenantId: $tenantId,
            stripePaymentIntentId: $invoice['payment_intent'] ?? '',
            amount: $invoice['amount_paid'] / 100, // Stripe envía en centavos
            currency: strtoupper($invoice['currency'] ?? 'usd'),
            plan: $metadata['plan'] ?? 'starter'
        );
        
        $payment->markAsSucceeded(
            stripeInvoiceId: $invoice['id'],
            receiptUrl: $invoice['hosted_invoice_url'] ?? ''
        );
        
        $this->paymentRepository->save($payment);
    }

    private function handleInvoicePaymentFailed(array $payload): void
    {
        $invoice = $payload['data']['object'] ?? [];
        $subscription = $invoice['subscription'] ?? [];
        $metadata = $subscription['metadata'] ?? [];
        
        $tenantId = TenantId::fromString($metadata['tenant_id'] ?? '');
        $tenant = $this->tenantRepository->findById($tenantId);
        
        if ($tenant) {
            // Notificar al tenant sobre el fallo de pago
            // No suspender inmediatamente, dar período de gracia
        }
    }

    private function handleSubscriptionDeleted(array $payload): void
    {
        $subscription = $payload['data']['object'] ?? [];
        $metadata = $subscription['metadata'] ?? [];
        
        $tenantId = TenantId::fromString($metadata['tenant_id'] ?? '');
        $tenant = $this->tenantRepository->findById($tenantId);
        
        if ($tenant) {
            $tenant->cancel();
            $this->tenantRepository->save($tenant);
        }
    }
}
