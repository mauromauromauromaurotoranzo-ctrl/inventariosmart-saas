<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Onboarding\CompleteStepRequest;
use App\Application\UseCases\Onboarding\CompleteStepUseCase;
use App\Application\UseCases\Onboarding\GetOnboardingStatusRequest;
use App\Application\UseCases\Onboarding\GetOnboardingStatusUseCase;
use App\Application\UseCases\Onboarding\StartOnboardingRequest;
use App\Application\UseCases\Onboarding\StartOnboardingUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly StartOnboardingUseCase $startOnboardingUseCase,
        private readonly GetOnboardingStatusUseCase $getOnboardingStatusUseCase,
        private readonly CompleteStepUseCase $completeStepUseCase
    ) {}

    /**
     * Iniciar o recuperar el onboarding del tenant actual
     */
    public function start(): JsonResponse
    {
        $tenant = Auth::user()->tenant ?? request()->attributes->get('tenant');
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant no encontrado'], 404);
        }

        $request = new StartOnboardingRequest(tenantId: $tenant->id()->value());
        $response = $this->startOnboardingUseCase->execute($request);

        if (!$response->success) {
            return response()->json(['error' => $response->error], 400);
        }

        return response()->json([
            'onboarding_id' => $response->onboardingId,
            'current_step' => $response->currentStep,
            'progress_percentage' => $response->progressPercentage,
            'step_config' => $response->stepConfig,
        ]);
    }

    /**
     * Obtener el estado actual del onboarding
     */
    public function status(): JsonResponse
    {
        $tenant = Auth::user()->tenant ?? request()->attributes->get('tenant');
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant no encontrado'], 404);
        }

        $request = new GetOnboardingStatusRequest(tenantId: $tenant->id()->value());
        $response = $this->getOnboardingStatusUseCase->execute($request);

        return response()->json([
            'has_started' => $response->hasStarted,
            'onboarding_id' => $response->onboardingId,
            'current_step' => $response->currentStep,
            'completed_steps' => $response->completedSteps,
            'progress_percentage' => $response->progressPercentage,
            'is_completed' => $response->isCompleted,
            'step_config' => $response->stepConfig,
            'started_at' => $response->startedAt,
            'completed_at' => $response->completedAt,
        ]);
    }

    /**
     * Completar el paso actual y avanzar al siguiente
     */
    public function completeStep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'onboarding_id' => 'required|string',
            'step_data' => 'nullable|array',
        ]);

        $useCaseRequest = new CompleteStepRequest(
            onboardingId: $validated['onboarding_id'],
            stepData: $validated['step_data'] ?? null
        );

        $response = $this->completeStepUseCase->execute($useCaseRequest);

        if (!$response->success) {
            return response()->json(['error' => $response->error], 400);
        }

        return response()->json([
            'onboarding_id' => $response->onboardingId,
            'current_step' => $response->currentStep,
            'completed_steps' => $response->completedSteps,
            'progress_percentage' => $response->progressPercentage,
            'is_completed' => $response->isCompleted,
            'step_config' => $response->stepConfig,
        ]);
    }

    /**
     * Vista del wizard de onboarding (SPA)
     */
    public function showWizard()
    {
        return view('onboarding.wizard');
    }
}
