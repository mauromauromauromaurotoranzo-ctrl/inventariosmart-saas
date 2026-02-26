<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Payment\CreateCheckoutSessionRequest;
use App\Application\UseCases\Payment\CreateCheckoutSessionUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutSessionUseCase $createCheckoutSessionUseCase
    ) {}

    /**
     * Crear sesión de checkout de Stripe
     */
    public function createCheckoutSession(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional,business',
        ]);

        $tenant = Auth::user()->tenant ?? request()->attributes->get('tenant');
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant no encontrado'], 404);
        }

        $useCaseRequest = new CreateCheckoutSessionRequest(
            tenantId: $tenant->id()->value(),
            plan: $validated['plan']
        );

        $response = $this->createCheckoutSessionUseCase->execute($useCaseRequest);

        if (!$response->success) {
            return response()->json(['error' => $response->error], 400);
        }

        // Si es AJAX, retornar JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'session_id' => $response->sessionId,
                'checkout_url' => $response->checkoutUrl,
            ]);
        }

        // Si es form normal, redirigir a Stripe
        return redirect($response->checkoutUrl);
    }

    /**
     * Página de éxito después del pago
     */
    public function success(Request $request, string $tenantSlug)
    {
        $sessionId = $request->get('session_id');
        
        return view('payment.success', [
            'tenantSlug' => $tenantSlug,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Página de cancelación del pago
     */
    public function cancel(string $tenantSlug)
    {
        return view('payment.cancel', [
            'tenantSlug' => $tenantSlug,
        ]);
    }

    /**
     * Mostrar historial de pagos del tenant
     */
    public function history()
    {
        $tenant = Auth::user()->tenant ?? request()->attributes->get('tenant');
        
        if (!$tenant) {
            abort(404, 'Tenant no encontrado');
        }

        return view('payment.history', [
            'tenant' => $tenant,
        ]);
    }
}
