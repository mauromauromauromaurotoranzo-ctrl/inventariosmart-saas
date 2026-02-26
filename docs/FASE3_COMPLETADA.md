# Fase 3 Completada - Onboarding Wizard

## Resumen

Se implementó el sistema completo de onboarding (configuración inicial) siguiendo la arquitectura hexagonal/DDD.

## Componentes Implementados

### Domain Layer

#### Entities
- `OnboardingProgress` - Entidad que representa el progreso del onboarding
  - Maneja pasos completados y actuales
  - Soporta skip de pasos opcionales
  - Calcula porcentaje de progreso
  - Configuración específica por rubro

#### Repository Interfaces
- `OnboardingRepositoryInterface` - Contrato para persistencia

### Application Layer (Use Cases)

1. **StartOnboardingUseCase**
   - Inicia o recupera el onboarding de un tenant
   - Valida que el tenant exista
   - Previene duplicados

2. **GetOnboardingStatusUseCase**
   - Consulta el estado actual del onboarding
   - Retorna progreso y configuración del paso actual

3. **CompleteStepUseCase**
   - Marca el paso actual como completado
   - Avanza al siguiente paso automáticamente
   - Detecta cuando el onboarding finaliza

### Infrastructure Layer

#### Repositories
- `EloquentOnboardingRepository` - Implementación con Eloquent

#### Models
- `OnboardingProgress` (Eloquent) - Modelo de base de datos

#### Controllers
- `OnboardingController` - API endpoints para el wizard

#### Middleware
- `IdentifyTenant` - Identifica el tenant por subdominio
  - Conecta a la BD del tenant dinámicamente
  - Valida trial/suscripción activa
  - Excluye subdominios reservados

### Presentation Layer

#### Views
- `onboarding/wizard.blade.php` - Interfaz SPA del wizard
  - Progreso visual con barra animada
  - Iconos dinámicos según el paso
  - Formularios adaptativos por tipo de paso:
    - Crear/Importar productos
    - Configurar caja (moneda, formas de pago)
    - Demo de venta
    - Revisión final
  - Soporte para saltar pasos opcionales
  - Estado de completado con celebración

#### Rutas
```php
GET  /onboarding           -> Vista del wizard
POST /api/onboarding/start -> Iniciar onboarding
GET  /api/onboarding/status-> Estado actual
POST /api/onboarding/complete-step -> Completar paso
```

### Database

#### Migración
- `2025_02_27_060000_create_onboarding_progress_table`
  - Campos: id, tenant_id, current_step, completed_steps, step_data, timestamps

## Pasos por Rubro

| Rubro | Pasos de Onboarding |
|-------|---------------------|
| retail | productos → caja → ventas |
| farmacia | medicamentos → proveedores → obras_sociales |
| restaurante | platos → insumos → proveedores |
| ferreteria | categorias → productos → clientes |
| moda | productos → variantes → temporada |
| distribuidora | productos → clientes → rutas |
| manufactura | materia_prima → productos_terminados → recetas |

## Flujo de Uso

1. Usuario se registra y crea tenant
2. Al primer login, redirige a `/onboarding`
3. El wizard carga automáticamente el onboarding según el rubro
4. Usuario completa cada paso (puede saltar los opcionales)
5. Al completar todos los pasos, muestra pantalla de éxito
6. Redirección al dashboard

## Próximos Pasos (Fase 4)

La Fase 4 incluye:
- Integración Stripe/MercadoPago
- Webhooks de pago
- Manejo de suscripciones
- Suspensión por falta de pago

## Archivos Creados/Modificados

```
app/
├── Domain/
│   ├── Entities/OnboardingProgress.php (NEW)
│   └── RepositoryInterfaces/OnboardingRepositoryInterface.php (NEW)
├── Application/
│   └── UseCases/Onboarding/
│       ├── StartOnboardingUseCase.php (NEW)
│       ├── StartOnboardingRequest.php (NEW)
│       ├── StartOnboardingResponse.php (NEW)
│       ├── CompleteStepUseCase.php (NEW)
│       ├── CompleteStepRequest.php (NEW)
│       ├── CompleteStepResponse.php (NEW)
│       ├── GetOnboardingStatusUseCase.php (NEW)
│       ├── GetOnboardingStatusRequest.php (NEW)
│       └── GetOnboardingStatusResponse.php (NEW)
├── Infrastructure/
│   └── Repositories/EloquentOnboardingRepository.php (NEW)
├── Http/
│   ├── Controllers/OnboardingController.php (MODIFIED)
│   └── Middleware/IdentifyTenant.php (NEW)
├── Models/OnboardingProgress.php (NEW)
└── Providers/RepositoryServiceProvider.php (MODIFIED)

database/migrations/2025_02_27_060000_create_onboarding_progress_table.php (NEW)

resources/views/onboarding/wizard.blade.php (NEW)

routes/web.php (MODIFIED)
```
