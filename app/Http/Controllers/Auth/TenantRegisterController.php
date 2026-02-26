<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class TenantRegisterController extends Controller
{
    /**
     * Registro de nuevo tenant con auto-provisioning
     */
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255', 'min:3'],
            'rubro' => ['required', 'in:retail,farmacia,restaurante,ferreteria,moda,distribuidora,manufactura'],
            'plan' => ['required', 'in:starter,professional,business,enterprise'],
            'email' => ['required', 'email', 'unique:tenants,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['required', 'accepted'],
        ], [
            'business_name.required' => 'El nombre del negocio es obligatorio',
            'rubro.required' => 'Selecciona un tipo de negocio',
            'rubro.in' => 'El tipo de negocio no es válido',
            'plan.required' => 'Selecciona un plan',
            'email.unique' => 'Este email ya está registrado',
            'terms.accepted' => 'Debes aceptar los términos y condiciones',
        ]);

        try {
            DB::beginTransaction();

            // Generar slug único
            $slug = $this->generateUniqueSlug($validated['business_name']);
            
            // Generar nombre de base de datos único
            $database = $this->generateUniqueDatabaseName($slug);

            // Crear tenant en la base de datos principal
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'slug' => $slug,
                'rubro' => $validated['rubro'],
                'database' => $database,
                'plan' => $validated['plan'],
                'trial_ends_at' => now()->addDays(14),
                'status' => 'active',
                'settings' => Tenant::getDefaultSettings($validated['rubro']),
                'email' => $validated['email'],
            ]);

            Log::info("Tenant creado en BD principal: {$tenant->slug}");

            // Crear base de datos física
            $tenant->createDatabase();
            
            Log::info("Base de datos creada: {$database}");

            // Conectar a la BD del tenant y ejecutar migraciones
            $tenant->connect();
            $this->runTenantMigrations($tenant);
            
            Log::info("Migraciones ejecutadas para: {$tenant->slug}");

            // Crear usuario administrador en la BD del tenant
            $this->createAdminUser($tenant, $validated);
            
            Log::info("Usuario admin creado para: {$tenant->slug}");

            // Seed inicial según rubro
            $this->seedInitialData($tenant);

            DB::commit();

            // Enviar email de bienvenida con credenciales
            try {
                \Mail::to($validated['email'])->queue(
                    new \App\Mail\TenantWelcome($tenant, $validated['password'])
                );
                Log::info("Email de bienvenida enviado a: {$validated['email']}");
            } catch (\Exception $e) {
                Log::error("Error enviando email de bienvenida: " . $e->getMessage());
                // No fallar el registro por error de email
            }

            return response()->json([
                'success' => true,
                'message' => '¡Tu cuenta ha sido creada exitosamente! Revisa tu email para acceder.',
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'url' => $tenant->getUrl(),
                        'rubro' => $tenant->rubro,
                        'plan' => $tenant->plan,
                    ],
                    'trial' => [
                        'ends_at' => $tenant->trial_ends_at->toIso8601String(),
                        'days_left' => now()->diffInDays($tenant->trial_ends_at),
                    ],
                    'next_steps' => [
                        'login_url' => $tenant->getUrl() . '/login',
                        'onboarding' => true,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Error creando tenant: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Limpiar en caso de error
            if (isset($tenant)) {
                try {
                    $tenant->deleteDatabase();
                    $tenant->delete();
                } catch (\Exception $cleanupError) {
                    Log::error("Error limpiando tenant fallido: " . $cleanupError->getMessage());
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cuenta. Por favor intenta nuevamente.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug(string $businessName): string
    {
        $baseSlug = Str::slug($businessName);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Generar nombre único de base de datos
     */
    private function generateUniqueDatabaseName(string $slug): string
    {
        return 'tenant_' . $slug . '_' . Str::random(6);
    }

    /**
     * Ejecutar migraciones en la BD del tenant
     */
    private function runTenantMigrations(Tenant $tenant): void
    {
        // Asegurar conexión
        $tenant->connect();
        
        // Ejecutar migraciones
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
        
        // Verificar que las tablas se crearon
        $tables = DB::connection('tenant')->select('SHOW TABLES');
        Log::info("Tablas creadas en {$tenant->database}: " . count($tables));
    }

    /**
     * Crear usuario administrador
     */
    private function createAdminUser(Tenant $tenant, array $data): void
    {
        DB::connection('tenant')->table('users')->insert([
            'name' => 'Administrador',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Seed datos iniciales según rubro
     */
    private function seedInitialData(Tenant $tenant): void
    {
        $seeds = [
            'retail' => [\Database\Seeders\RetailSeeder::class],
            'farmacia' => [\Database\Seeders\FarmaciaSeeder::class],
            'restaurante' => [\Database\Seeders\RestauranteSeeder::class],
            // ... más seeders por rubro
        ];
        
        if (isset($seeds[$tenant->rubro])) {
            foreach ($seeds[$tenant->rubro] as $seeder) {
                (new $seeder)->run();
            }
        }
        
        // Seed común para todos
        (new \Database\Seeders\CommonSeeder)->run();
    }

    /**
     * Verificar disponibilidad de slug
     */
    public function checkSlugAvailability(Request $request)
    {
        $request->validate(['slug' => 'required|string|min:3|max:50']);
        
        $slug = Str::slug($request->slug);
        $available = !Tenant::where('slug', $slug)->exists();
        
        return response()->json([
            'slug' => $slug,
            'available' => $available,
            'suggestions' => $available ? [] : $this->generateSlugSuggestions($slug),
        ]);
    }

    /**
     * Generar sugerencias de slug
     */
    private function generateSlugSuggestions(string $baseSlug): array
    {
        $suggestions = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $suggestion = $baseSlug . '-' . $i;
            if (!Tenant::where('slug', $suggestion)->exists()) {
                $suggestions[] = $suggestion;
            }
        }
        
        // Agregar año actual
        $yearSuggestion = $baseSlug . '-' . date('Y');
        if (!Tenant::where('slug', $yearSuggestion)->exists()) {
            $suggestions[] = $yearSuggestion;
        }
        
        return array_slice($suggestions, 0, 3);
    }
}
