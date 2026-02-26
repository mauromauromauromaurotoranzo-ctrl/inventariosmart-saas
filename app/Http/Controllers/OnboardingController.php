<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Obtener estado del onboarding
     */
    public function status(Request $request)
    {
        $tenant = app('tenant');
        $settings = $tenant->settings ?? [];
        
        return response()->json([
            'completed' => $settings['onboarding_completed'] ?? false,
            'current_step' => $settings['onboarding_step'] ?? 1,
            'total_steps' => $this->getTotalSteps($tenant->rubro),
            'steps' => $this->getSteps($tenant),
            'progress' => $this->calculateProgress($tenant),
        ]);
    }

    /**
     * Guardar progreso de un paso
     */
    public function saveStep(Request $request, int $stepNumber)
    {
        $tenant = app('tenant');
        
        $validated = $request->validate([
            'data' => 'required|array',
            'completed' => 'boolean',
        ]);

        $settings = $tenant->settings ?? [];
        
        // Guardar datos del paso
        $settings["onboarding_step_{$stepNumber}"] = $validated['data'];
        
        // Actualizar paso actual
        if ($validated['completed'] ?? false) {
            $settings['onboarding_step'] = $stepNumber + 1;
        }
        
        // Verificar si completó todos los pasos
        $totalSteps = $this->getTotalSteps($tenant->rubro);
        if ($stepNumber >= $totalSteps) {
            $settings['onboarding_completed'] = true;
            $settings['onboarding_completed_at'] = now()->toIso8601String();
        }
        
        $tenant->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'next_step' => $settings['onboarding_step'],
            'completed' => $settings['onboarding_completed'] ?? false,
        ]);
    }

    /**
     * Completar onboarding (skip o finish)
     */
    public function complete(Request $request)
    {
        $tenant = app('tenant');
        
        $settings = $tenant->settings ?? [];
        $settings['onboarding_completed'] = true;
        $settings['onboarding_completed_at'] = now()->toIso8601String();
        $settings['onboarding_skipped'] = $request->boolean('skipped', false);
        
        $tenant->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Obtener configuración específica del paso
     */
    public function getStepConfig(int $stepNumber)
    {
        $tenant = app('tenant');
        
        $config = match($stepNumber) {
            1 => $this->getStep1Config($tenant), // Información básica
            2 => $this->getStep2Config($tenant), // Productos/Items
            3 => $this->getStep3Config($tenant), // Configuración específica
            default => null,
        };

        if (!$config) {
            return response()->json(['error' => 'Paso no encontrado'], 404);
        }

        return response()->json($config);
    }

    /**
     * Obtener total de pasos según rubro
     */
    private function getTotalSteps(string $rubro): int
    {
        return match($rubro) {
            'retail' => 3,
            'farmacia' => 4,
            'restaurante' => 4,
            'ferreteria' => 3,
            'moda' => 4,
            'distribuidora' => 4,
            'manufactura' => 5,
            default => 3,
        };
    }

    /**
     * Obtener lista de pasos con estado
     */
    private function getSteps(Tenant $tenant): array
    {
        $rubro = $tenant->rubro;
        $settings = $tenant->settings ?? [];
        $currentStep = $settings['onboarding_step'] ?? 1;
        
        $steps = [
            'retail' => [
                ['id' => 1, 'title' => 'Tu Negocio', 'icon' => '🏪'],
                ['id' => 2, 'title' => 'Productos', 'icon' => '📦'],
                ['id' => 3, 'title' => 'Primera Venta', 'icon' => '💰'],
            ],
            'farmacia' => [
                ['id' => 1, 'title' => 'Tu Farmacia', 'icon' => '💊'],
                ['id' => 2, 'title' => 'Medicamentos', 'icon' => '💉'],
                ['id' => 3, 'title' => 'Obras Sociales', 'icon' => '🏥'],
                ['id' => 4, 'title' => 'Primera Receta', 'icon' => '📝'],
            ],
            'restaurante' => [
                ['id' => 1, 'title' => 'Tu Restaurante', 'icon' => '🍽️'],
                ['id' => 2, 'title' => 'Menú', 'icon' => '📋'],
                ['id' => 3, 'title' => 'Insumos', 'icon' => '🥘'],
                ['id' => 4, 'title' => 'Áreas', 'icon' => '👨‍🍳'],
            ],
            'ferreteria' => [
                ['id' => 1, 'title' => 'Tu Ferretería', 'icon' => '🔧'],
                ['id' => 2, 'title' => 'Categorías', 'icon' => '📁'],
                ['id' => 3, 'title' => 'Listas de Precios', 'icon' => '💵'],
            ],
            'moda' => [
                ['id' => 1, 'title' => 'Tu Tienda', 'icon' => '👗'],
                ['id' => 2, 'title' => 'Prendas', 'icon' => '👕'],
                ['id' => 3, 'title' => 'Tallas y Colores', 'icon' => '🎨'],
                ['id' => 4, 'title' => 'Temporadas', 'icon' => '📅'],
            ],
            'distribuidora' => [
                ['id' => 1, 'title' => 'Tu Distribuidora', 'icon' => '🚚'],
                ['id' => 2, 'title' => 'Catálogo', 'icon' => '📖'],
                ['id' => 3, 'title' => 'Clientes', 'icon' => '👥'],
                ['id' => 4, 'title' => 'Rutas', 'icon' => '🗺️'],
            ],
            'manufactura' => [
                ['id' => 1, 'title' => 'Tu Fábrica', 'icon' => '🏭'],
                ['id' => 2, 'title' => 'Materia Prima', 'icon' => '📦'],
                ['id' => 3, 'title' => 'Recetas (BOM)', 'icon' => '⚙️'],
                ['id' => 4, 'title' => 'Productos Terminados', 'icon' => '🎁'],
                ['id' => 5, 'title' => 'Órdenes', 'icon' => '📋'],
            ],
        };

        $stepList = $steps[$rubro] ?? $steps['retail'];
        
        // Agregar estado a cada paso
        foreach ($stepList as &$step) {
            $step['status'] = match(true) {
                $step['id'] < $currentStep => 'completed',
                $step['id'] === $currentStep => 'current',
                default => 'pending',
            };
        }

        return $stepList;
    }

    /**
     * Calcular progreso
     */
    private function calculateProgress(Tenant $tenant): int
    {
        $settings = $tenant->settings ?? [];
        $currentStep = $settings['onboarding_step'] ?? 1;
        $totalSteps = $this->getTotalSteps($tenant->rubro);
        
        return min(100, intval((($currentStep - 1) / $totalSteps) * 100));
    }

    /**
     * Configuración paso 1: Información básica
     */
    private function getStep1Config(Tenant $tenant): array
    {
        return [
            'title' => 'Información de tu negocio',
            'description' => 'Completa los datos básicos para personalizar tu experiencia.',
            'fields' => [
                ['name' => 'business_name', 'label' => 'Nombre del negocio', 'type' => 'text', 'required' => true],
                ['name' => 'address', 'label' => 'Dirección', 'type' => 'text', 'required' => false],
                ['name' => 'phone', 'label' => 'Teléfono', 'type' => 'tel', 'required' => false],
                ['name' => 'currency', 'label' => 'Moneda', 'type' => 'select', 'options' => ['USD', 'ARS', 'MXN', 'COP', 'CLP', 'PEN'], 'required' => true],
                ['name' => 'tax_id', 'label' => 'Identificación fiscal', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * Configuración paso 2: Productos
     */
    private function getStep2Config(Tenant $tenant): array
    {
        $rubroSpecific = match($tenant->rubro) {
            'farmacia' => [
                'title' => 'Registra tus medicamentos',
                'description' => 'Puedes agregarlos manualmente o importar desde Excel.',
                'can_import' => true,
                'sample_fields' => ['nombre', 'codigo', 'precio', 'stock', 'lote', 'vencimiento'],
            ],
            'restaurante' => [
                'title' => 'Crea tu menú',
                'description' => 'Agrega platos y sus ingredientes. Calculamos costos automáticamente.',
                'can_import' => false,
            ],
            default => [
                'title' => 'Carga tus productos',
                'description' => 'Puedes agregarlos manualmente, escanear códigos de barras o importar desde Excel.',
                'can_import' => true,
                'sample_fields' => ['nombre', 'codigo', 'precio', 'stock', 'categoria'],
            ],
        };

        return array_merge([
            'quick_add' => true,
            'show_tutorial' => true,
        ], $rubroSpecific);
    }

    /**
     * Configuración paso 3: Configuración específica
     */
    private function getStep3Config(Tenant $tenant): array
    {
        return match($tenant->rubro) {
            'farmacia' => [
                'title' => 'Configura obras sociales',
                'description' => 'Agrega las obras sociales con las que trabajas.',
                'component' => 'ObrasSocialesSetup',
            ],
            'restaurante' => [
                'title' => 'Configura áreas de cocina',
                'description' => 'Define bar, cocina caliente, parrilla, etc.',
                'component' => 'AreasCocinaSetup',
            ],
            'distribuidora' => [
                'title' => 'Configura listas de precios',
                'description' => 'Mayorista, minorista, constructoras...',
                'component' => 'ListasPreciosSetup',
            ],
            default => [
                'title' => 'Configura tu primera caja',
                'description' => 'Todo listo para tu primera venta.',
                'component' => 'CajaSetup',
            ],
        };
    }
}
