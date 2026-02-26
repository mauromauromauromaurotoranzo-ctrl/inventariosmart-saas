<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Tenant;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'tenant_slug' => 'required|string',
            'plan' => 'required|in:basic,pro,enterprise',
        ]);

        $tenant = Tenant::where('slug', $request->tenant_slug)->first();
        
        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant no encontrado');
        }

        $prices = [
            'basic' => config('services.stripe.prices.basic'),
            'pro' => config('services.stripe.prices.pro'),
            'enterprise' => config('services.stripe.prices.enterprise'),
        ];

        $priceId = $prices[$request->plan];

        if (!$priceId) {
            return redirect()->back()->with('error', 'Plan no configurado');
        }

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('payment.success', ['tenant' => $tenant->slug]),
                'cancel_url' => route('payment.cancel', ['tenant' => $tenant->slug]),
                'metadata' => [
                    'tenant_slug' => $tenant->slug,
                    'plan' => $request->plan,
                ],
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el pago');
        }
    }

    public function success(string $tenantSlug)
    {
        return view('payment.success', compact('tenantSlug'));
    }

    public function cancel(string $tenantSlug)
    {
        return view('payment.cancel', compact('tenantSlug'));
    }
}
