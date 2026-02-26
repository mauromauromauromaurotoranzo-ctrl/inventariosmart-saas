<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Application\UseCases\Payment\ProcessStripeWebhookUseCase;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly ProcessStripeWebhookUseCase $webhookUseCase
    ) {}

    public function handle(Request $request)
    {
        // Verificar firma del webhook (en producción)
        $payload = $request->all();
        
        Log::info('Stripe webhook received', ['type' => $payload['type'] ?? 'unknown']);

        try {
            $this->webhookUseCase->execute($payload);
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
