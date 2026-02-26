<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

use App\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use App\Domain\ValueObjects\TenantId;
use Stripe\StripeClient;

final class CreateCheckoutSessionUseCase
{
    private StripeClient $stripe;

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        ?StripeClient $stripe = null
    ) {
        $this->stripe = $stripe ?? new StripeClient(config('services.stripe.secret'));
    }

    public function execute(CreateCheckoutSessionRequest $request): CreateCheckoutSessionResponse
    {
        $tenantId = TenantId::fromString($request->tenantId);
        $tenant = $this->tenantRepository->findById($tenantId);

        if (!$tenant) {
            return CreateCheckoutSessionResponse::error('Tenant no encontrado');
        }

        try {
            // Obtener el price ID según el plan
            $priceId = $this->getPriceIdForPlan($request->plan);
            
            if (!$priceId) {
                return CreateCheckoutSessionResponse::error('Plan no válido');
            }

            // Crear sesión de checkout
            $session = $this->stripe->checkout->sessions->create([
                'customer_email' => $tenant->email(),
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('payment.success', ['tenant' => $tenant->slug()->value()]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', ['tenant' => $tenant->slug()->value()]),
                'metadata' => [
                    'tenant_id' => $tenant->id()->value(),
                    'plan' => $request->plan,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'tenant_id' => $tenant->id()->value(),
                        'plan' => $request->plan,
                    ],
                ],
            ]);

            return CreateCheckoutSessionResponse::success(
                sessionId: $session->id,
                checkoutUrl: $session->url
            );

        } catch (\Exception $e) {
            return CreateCheckoutSessionResponse::error('Error al crear sesión de pago: ' . $e->getMessage());
        }
    }

    private function getPriceIdForPlan(string $plan): ?string
    {
        return match ($plan) {
            'starter' => config('services.stripe.prices.starter'),
            'professional' => config('services.stripe.prices.professional'),
            'business' => config('services.stripe.prices.business'),
            default => null,
        };
    }
}
