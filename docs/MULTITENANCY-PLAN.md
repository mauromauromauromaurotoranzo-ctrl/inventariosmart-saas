# Implementación Multi-Tenancy - InventarioSmart SaaS

## Arquitectura Propuesta

### ¿Qué es Multi-Tenancy?
Una sola instancia de la aplicación sirve a múltiples clientes (tenants), cada uno con:
- Base de datos separada (o esquema separado)
- Subdominio propio: `cliente.inventariosmart.app`
- Configuración específica por rubro
- Datos completamente aislados

### Modelo: Database-per-Tenant

```
┌─────────────────────────────────────────┐
│         InventarioSmart App             │
│  (Una sola instancia Laravel)           │
└─────────────┬───────────────────────────┘
              │
    ┌─────────┼─────────┐
    │         │         │
┌───▼───┐ ┌──▼────┐ ┌──▼────┐
│tenant_│ │tenant_│ │tenant_│
│farmacia│ │retail_│ │restaur│
│_001_db │ │001_db │ │ante_01│
└────────┘ └───────┘ └───────┘
```

---

## Paso 1: Configuración de Base de Datos

### 1.1 Migración para tabla `tenants`

```php
// database/migrations/2025_02_27_000000_create_tenants_table.php
Schema::create('tenants', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Nombre del negocio
    $table->string('slug')->unique(); // Subdominio: farmacia-san-juan
    $table->string('rubro'); // farmacia, retail, restaurante, etc
    $table->string('database')->unique(); // tenant_farmacia_001
    $table->string('plan'); // starter, professional, business
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('subscribed_at')->nullable();
    $table->string('status')->default('active'); // active, suspended, cancelled
    $table->json('settings')->nullable(); // Config específica
    $table->timestamps();
});
```

### 1.2 Modelo Tenant

```php
// app/Models/Tenant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'rubro', 'database', 
        'plan', 'status', 'settings'
    ];
    
    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
    ];
    
    // Conectar a la base de datos del tenant
    public function connect()
    {
        config(['database.connections.tenant.database' => $this->database]);
        DB::purge('tenant');
        DB::reconnect('tenant');
        return $this;
    }
    
    // Crear base de datos del tenant
    public function createDatabase()
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS {$this->database}");
        return $this;
    }
    
    // Verificar si está en trial
    public function isOnTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
    
    // Verificar si está activo
    public function isActive()
    {
        return $this->status === 'active';
    }
}
```

---

## Paso 2: Middleware de Identificación de Tenant

```php
// app/Http/Middleware/IdentifyTenant.php
namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class IdentifyTenant
{
    public function handle($request, Closure $next)
    {
        // Obtener subdominio: farmacia-san-juan.inventariosmart.app
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        // Excluir subdominios reservados
        if (in_array($subdomain, ['www', 'app', 'admin', 'api'])) {
            return $next($request);
        }
        
        // Buscar tenant
        $tenant = Tenant::where('slug', $subdomain)
            ->where('status', 'active')
            ->first();
        
        if (!$tenant) {
            abort(404, 'Tenant no encontrado');
        }
        
        // Verificar trial/suscripción
        if (!$tenant->isOnTrial() && !$tenant->subscribed_at) {
            abort(403, 'Suscripción requerida');
        }
        
        // Conectar a BD del tenant
        $tenant->connect();
        
        // Guardar en request para uso posterior
        $request->merge(['tenant' => $tenant]);
        app()->instance('tenant', $tenant);
        
        return $next($request);
    }
}
```

---

## Paso 3: Auto-Provisioning (Registro Automático)

### 3.1 Controlador de Registro

```php
// app/Http/Controllers/Auth/RegisterController.php
namespace App\Http\Controllers\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'rubro' => 'required|in:retail,farmacia,restaurante,ferreteria,moda,distribuidora,manufactura',
            'plan' => 'required|in:starter,professional,business',
            'email' => 'required|email|unique:tenants,email',
            'password' => 'required|min:8',
        ]);
        
        // Generar slug único
        $slug = Str::slug($validated['business_name']);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Generar nombre de BD único
        $database = 'tenant_' . $slug . '_' . Str::random(4);
        
        // Crear tenant
        $tenant = Tenant::create([
            'name' => $validated['business_name'],
            'slug' => $slug,
            'rubro' => $validated['rubro'],
            'database' => $database,
            'plan' => $validated['plan'],
            'trial_ends_at' => now()->addDays(14),
            'status' => 'active',
            'settings' => $this->getDefaultSettings($validated['rubro']),
        ]);
        
        // Crear base de datos
        $tenant->createDatabase();
        
        // Conectar y ejecutar migraciones
        $tenant->connect();
        $this->runMigrations($tenant);
        
        // Crear usuario administrador
        DB::connection('tenant')->table('users')->insert([
            'name' => 'Administrador',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Enviar email con credenciales
        // TODO: Implementar email
        
        return response()->json([
            'success' => true,
            'message' => 'Tenant creado exitosamente',
            'data' => [
                'subdomain' => $slug . '.inventariosmart.app',
                'trial_ends_at' => $tenant->trial_ends_at,
            ]
        ]);
    }
    
    private function getDefaultSettings($rubro)
    {
        $settings = [
            'retail' => [
                'features' => ['escaner', 'promociones', 'multi_sucursal'],
                'onboarding_steps' => ['productos', 'caja', 'ventas'],
            ],
            'farmacia' => [
                'features' => ['lotes', 'vencimientos', 'obras_sociales'],
                'onboarding_steps' => ['medicamentos', 'proveedores', 'obras_sociales'],
            ],
            'restaurante' => [
                'features' => ['recetas', 'mermas', 'insumos'],
                'onboarding_steps' => ['platos', 'insumos', 'proveedores'],
            ],
            // ... más rubros
        ];
        
        return $settings[$rubro] ?? $settings['retail'];
    }
    
    private function runMigrations(Tenant $tenant)
    {
        // Ejecutar migraciones en la BD del tenant
        $path = database_path('migrations/tenant');
        
        if (!is_dir($path)) {
            // Usar migraciones estándar
            $path = database_path('migrations');
        }
        
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
    }
}
```

---

## Paso 4: Configuración de Rutas

```php
// routes/tenant.php
// Estas rutas se cargan DESPUÉS de identificar el tenant

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Productos
    Route::apiResource('productos', ProductoController::class);
    
    // Ventas
    Route::apiResource('ventas', VentaController::class);
    
    // Clientes
    Route::apiResource('clientes', ClienteController::class);
    
    // Cajas
    Route::apiResource('cajas', CajaController::class);
    
    // ... más rutas específicas del tenant
    
});

// Rutas específicas por rubro
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    // Farmacia
    Route::get('/lotes/vencimientos', [LoteController::class, 'vencimientos'])
        ->middleware('rubro:farmacia');
    
    // Restaurante
    Route::apiResource('recetas', RecetaController::class)
        ->middleware('rubro:restaurante');
    
    // Distribuidora
    Route::get('/clientes/{cliente}/lista-precios', [ClienteController::class, 'listaPrecios'])
        ->middleware('rubro:distribuidora');
});
```

---

## Paso 5: Middleware de Rubro

```php
// app/Http/Middleware/CheckRubro.php
namespace App\Http\Middleware;

use Closure;

class CheckRubro
{
    public function handle($request, Closure $next, $rubro)
    {
        $tenant = app('tenant');
        
        if ($tenant->rubro !== $rubro) {
            abort(403, 'Esta función no está disponible para tu rubro');
        }
        
        return $next($request);
    }
}
```

---

## Paso 6: Configuración de Base de Datos

```php
// config/database.php
'return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        
        // Base de datos principal (tenants, usuarios globales)
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            // ... resto de config
        ],
        
        // Conexión dinámica para tenants
        'tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => null, // Se setea dinámicamente
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            // ... resto de config
        ],
        
    ],
];
```

---

## Paso 7: Modelos Tenant-Aware

```php
// app/Models/TenantModel.php (Base para todos los modelos)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class TenantModel extends Model
{
    // Forzar uso de conexión tenant
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = 'tenant';
    }
    
    // Scope para asegurar tenant
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Agregar tenant_id si es necesario
            if (app()->has('tenant')) {
                $model->tenant_id = app('tenant')->id;
            }
        });
    }
}

// Ejemplo: Producto
class Producto extends TenantModel
{
    protected $fillable = [
        'nombre', 'codigo', 'precio', 'stock', 
        'categoria_id', 'rubro_specific_data'
    ];
    
    protected $casts = [
        'rubro_specific_data' => 'array', // Datos específicos por rubro
    ];
}
```

---

## Paso 8: Comandos Artisan para Gestión

```php
// app/Console/Commands/CreateTenant.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                            {name : Nombre del negocio}
                            {rubro : Tipo de rubro}
                            {--plan=starter : Plan (starter/professional/business)}';
    
    protected $description = 'Crear un nuevo tenant';
    
    public function handle()
    {
        $name = $this->argument('name');
        $rubro = $this->argument('rubro');
        $plan = $this->option('plan');
        
        // Lógica similar al controlador
        // ...
        
        $this->info("Tenant creado: {$tenant->slug}.inventariosmart.app");
    }
}

// app/Console/Commands/MigrateTenant.php
class MigrateTenant extends Command
{
    protected $signature = 'tenant:migrate {slug? : Slug del tenant}';
    
    public function handle()
    {
        if ($slug = $this->argument('slug')) {
            // Migrar un tenant específico
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            $tenant->connect();
            $this->call('migrate', ['--database' => 'tenant', '--force' => true]);
        } else {
            // Migrar todos los tenants
            Tenant::all()->each(function ($tenant) {
                $tenant->connect();
                $this->call('migrate', ['--database' => 'tenant', '--force' => true]);
                $this->info("Migrado: {$tenant->slug}");
            });
        }
    }
}
```

---

## Paso 9: Implementación Progresiva

### Fase 1: Estructura Base (Esta semana)
1. [ ] Crear migración `tenants`
2. [ ] Crear modelo `Tenant`
3. [ ] Crear middleware `IdentifyTenant`
4. [ ] Configurar conexión `tenant` en database.php
5. [ ] Crear base `TenantModel`

### Fase 2: Auto-Provisioning (Semana 2)
1. [ ] Controlador de registro
2. [ ] Creación automática de BD
3. [ ] Ejecución de migraciones
4. [ ] Email con credenciales
5. [ ] Landing page con formulario

### Fase 3: Onboarding (Semana 3)
1. [ ] Wizard por rubro
2. [ ] Configuración inicial
3. [ ] Tutorial interactivo
4. [ ] Importación de datos

### Fase 4: Billing (Semana 4)
1. [ ] Integración Stripe/MercadoPago
2. [ ] Webhooks de pago
3. [ ] Manejo de suscripciones
4. [ ] Suspensión por falta de pago

---

## 🚀 Empezamos con Fase 1

¿Quieres que comience implementando la estructura base?

1. Migración de tabla `tenants`
2. Modelo `Tenant`
3. Middleware de identificación
4. Configuración de BD

Dale el OK y empezamos! 💪
