<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Payment\HandleStripeWebhookRequest;
use App\Application\UseCases\Payment\HandleStripeWebhookUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly HandleStripeWebhookUseCase $webhookUseCase
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        // Verificar firma del webhook si estamos en producción
        if (app()->environment('production') && $secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
                $data = json_decode($payload, true);
            } catch (\Exception $e) {
                Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }
        } else {
            // En desarrollo, no verificamos la firma
            $data = json_decode($payload, true);
        }

        $eventType = $data['type'] ?? 'unknown';
        Log::info('Stripe webhook received', ['type' => $eventType]);

        try {
            $useCaseRequest = new HandleStripeWebhookRequest(
                eventType: $eventType,
                payload: $data
            );

            $response = $this->webhookUseCase->execute($useCaseRequest);

            if (!$response->success) {
                Log::error('Stripe webhook processing error: ' . $response->error);
                return response()->json(['status' => 'error', 'message' => $response->error], 500);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
