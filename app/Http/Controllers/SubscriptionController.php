<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Mostrar planes de suscripción
     */
    public function plans()
    {
        $plans = [
            'starter' => [
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 29,
                'description' => 'Para comenzar',
                'features' => [
                    'Hasta 1,000 productos',
                    '1 sucursal',
                    '3 usuarios',
                    'Soporte por email',
                    'App móvil básica',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_STARTER', 'price_starter'),
                'popular' => false,
            ],
            'professional' => [
                'id' => 'professional',
                'name' => 'Professional',
                'price' => 79,
                'description' => 'Para crecer',
                'features' => [
                    'Hasta 10,000 productos',
                    '5 sucursales',
                    '10 usuarios',
                    'Soporte prioritario',
                    'App móvil completa',
                    'Reportes avanzados',
                    'API access',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_PROFESSIONAL', 'price_professional'),
                'popular' => true,
            ],
            'business' => [
                'id' => 'business',
                'name' => 'Business',
                'price' => 149,
                'description' => 'Para empresas',
                'features' => [
                    'Productos ilimitados',
                    'Sucursales ilimitadas',
                    'Usuarios ilimitados',
                    'Soporte 24/7',
                    'App móvil personalizada',
                    'Business intelligence',
                    'API + Webhooks',
                    'Onboarding dedicado',
                ],
                'stripe_price_id' => env('STRIPE_PRICE_BUSINESS', 'price_business'),
                'popular' => false,
            ],
        ];

        return response()->json([
            'plans' => $plans,
            'currency' => 'USD',
        ]);
    }

    /**
     * Crear sesión de checkout Stripe
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional,business',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        
        // Verificar que no esté ya suscrito
        if ($tenant->subscribed_at) {
            return response()->json([
                'error' => 'Ya tienes una suscripción activa'
            ], 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $plans = $this->getPlans();
            $selectedPlan = $plans[$validated['plan']];

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "InventarioSmart {$selectedPlan['name']}",
                            'description' => $selectedPlan['description'],
                        ],
                        'unit_amount' => $selectedPlan['price'] * 100, // Centavos
                        'recurring' => [
                            'interval' => 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('subscription.success', [
                    'tenant_id' => $tenant->id,
                    'session_id' => '{CHECKOUT_SESSION_ID}',
                ]),
                'cancel_url' => route('subscription.cancel', [
                    'tenant_id' => $tenant->id,
                ]),
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan' => $validated['plan'],
                ],
                'customer_email' => $tenant->email,
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Error creando checkout Stripe: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al procesar el pago'
            ], 500);
        }
    }

    /**
     * Éxito en el pago
     */
    public function success(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'session_id' => 'required|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $session = StripeSession::retrieve($validated['session_id']);
            
            if ($session->payment_status === 'paid') {
                $tenant = Tenant::findOrFail($validated['tenant_id']);
                
                // Activar suscripción
                $tenant->markAsSubscribed();
                
                // Guardar datos de Stripe
                $tenant->update([
                    'settings' => array_merge($tenant->settings ?? [], [
                        'stripe_subscription_id' => $session->subscription,
                        'stripe_customer_id' => $session->customer,
                        'plan' => $session->metadata->plan,
                    ]),
                ]);

                Log::info("Suscripción activada para tenant: {$tenant->slug}");

                // Redirigir al dashboard del tenant
                return redirect()->away($tenant->getUrl() . '/onboarding?welcome=true');
            }

            return response()->json(['error' => 'Pago no completado'], 400);

        } catch (\Exception $e) {
            Log::error("Error en success callback: " . $e->getMessage());
            return response()->json(['error' => 'Error procesando pago'], 500);
        }
    }

    /**
     * Cancelación del checkout
     */
    public function cancel(Request $request)
    {
        $tenant = Tenant::findOrFail($request->tenant_id);
        
        return redirect()->away(
            config('app.frontend_url') . "/register?tenant={$tenant->slug}&status=cancelled"
        );
    }

    /**
     * Webhook de Stripe
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $sigHeader,
                $secret
            );

            Log::info("Webhook Stripe recibido: {$event->type}");

            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCancelled($event->data->object);
                    break;
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Error webhook Stripe: " . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 400);
        }
    }

    /**
     * Manejar pago exitoso
     */
    private function handlePaymentSucceeded($invoice)
    {
        $subscriptionId = $invoice->subscription;
        
        // Buscar tenant por subscription ID
        $tenant = Tenant::whereJsonContains('settings->stripe_subscription_id', $subscriptionId)
            ->first();
        
        if ($tenant) {
            Log::info("Pago exitoso confirmado para tenant: {$tenant->slug}");
            // Aquí podrías enviar email de confirmación
        }
    }

    /**
     * Manejar pago fallido
     */
    private function handlePaymentFailed($invoice)
    {
        $subscriptionId = $invoice->subscription;
        
        $tenant = Tenant::whereJsonContains('settings->stripe_subscription_id', $subscriptionId)
            ->first();
        
        if ($tenant) {
            Log::warning("Pago fallido para tenant: {$tenant->slug}");
            // Enviar email de alerta
            // Si falla múltiples veces, suspender
        }
    }

    /**
     * Manejar cancelación de suscripción
     */
    private function handleSubscriptionCancelled($subscription)
    {
        $tenant = Tenant::whereJsonContains('settings->stripe_subscription_id', $subscription->id)
            ->first();
        
        if ($tenant) {
            $tenant->cancel();
            Log::info("Suscripción cancelada para tenant: {$tenant->slug}");
        }
    }

    /**
     * Obtener planes
     */
    private function getPlans(): array
    {
        return [
            'starter' => [
                'name' => 'Starter',
                'price' => 29,
                'description' => 'Para comenzar',
            ],
            'professional' => [
                'name' => 'Professional',
                'price' => 79,
                'description' => 'Para crecer',
            ],
            'business' => [
                'name' => 'Business',
                'price' => 149,
                'description' => 'Para empresas',
            ],
        ];
    }
}
