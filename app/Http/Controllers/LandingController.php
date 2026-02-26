<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Tenant\RegisterTenantRequest;
use App\Application\UseCases\Tenant\RegisterTenantUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public function __construct(
        private readonly RegisterTenantUseCase $registerTenantUseCase
    ) {}

    public function index()
    {
        return view('landing.index');
    }

    public function pricing()
    {
        return view('landing.pricing');
    }

    public function showRegistrationForm()
    {
        $rubros = [
            'retail' => 'Retail / Tienda',
            'farmacia' => 'Farmacia',
            'restaurante' => 'Restaurante',
            'ferreteria' => 'Ferretería',
            'moda' => 'Moda / Indumentaria',
            'distribuidora' => 'Distribuidora',
            'manufactura' => 'Manufactura',
        ];

        $plans = [
            'basic' => 'Básico',
            'pro' => 'Profesional',
            'enterprise' => 'Empresarial',
        ];

        return view('landing.register', compact('rubros', 'plans'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|regex:/^[a-z0-9-]+$/|min:3|max:50|unique:tenants,slug',
            'rubro' => 'required|string|in:retail,farmacia,restaurante,ferreteria,moda,distribuidora,manufactura',
            'plan' => 'required|string|in:basic,pro,enterprise',
            'email' => 'required|email|max:255',
            'terms' => 'required|accepted',
        ], [
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'slug.unique' => 'Este nombre de tienda ya está en uso.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $useCaseRequest = new RegisterTenantRequest(
            name: $request->input('name'),
            slug: $request->input('slug'),
            rubro: $request->input('rubro'),
            plan: $request->input('plan'),
            email: $request->input('email')
        );

        $response = $this->registerTenantUseCase->execute($useCaseRequest);

        if (!$response->isSuccess()) {
            return redirect()->back()
                ->with('error', $response->error())
                ->withInput();
        }

        $tenant = $response->tenant();

        // Redirigir al onboarding
        return redirect()
            ->route('onboarding.start', ['tenant' => $tenant->slug()->value()])
            ->with('success', '¡Tu cuenta ha sido creada exitosamente!');
    }
}
