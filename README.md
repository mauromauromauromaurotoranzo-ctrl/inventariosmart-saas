# 🏪 InventarioSmart - Sistema de Gestión de Inventario SaaS

Sistema multi-tenant de gestión de inventario desarrollado con **Laravel 11**, **Blade**, **Alpine.js** y **Tailwind CSS**. Cada cliente tiene su propio subdominio y base de datos aislada.

## 🚀 Despliegue Rápido

### Requisitos Previos

- Docker y Docker Compose
- Git
- Dominio configurado con wildcard DNS (*.tudominio.com)
- Cuenta de Stripe (para suscripciones)

### Opción 1: Script Automático (Recomendado)

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/inventariosmart-saas.git
cd inventariosmart-saas

# 2. Configurar variables de entorno
cp .env.example .env
nano .env  # Editar configuraciones

# 3. Hacer ejecutables los scripts
chmod +x deploy.sh deploy-produccion.sh crear-tenant.sh

# 4. Desplegar
./deploy.sh produccion

# 5. Crear tenant principal (tu empresa)
./crear-tenant.sh tuempresa "Tu Empresa SA" admin@tuempresa.com password123
```

### Variables de Entorno Importantes (.env)

```env
# Aplicación
APP_NAME="InventarioSmart"
APP_ENV=production
APP_KEY=base64:GENERAR_CON_key:generate
APP_DEBUG=false
APP_URL=https://inventariosmart.com

# Base de datos central (landlord)
DB_HOST=db
DB_PORT=3306
DB_DATABASE=inventario_landlord
DB_USERNAME=root
DB_PASSWORD=tu_password_seguro

# Redis (para caché y sesiones)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (para notificaciones)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@tudominio.com
MAIL_PASSWORD=tu_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@inventariosmart.com
MAIL_FROM_NAME="InventarioSmart"

# Stripe (suscripciones)
STRIPE_KEY=pk_live_tu_key_publica
STRIPE_SECRET=sk_live_tu_key_secreta
STRIPE_WEBHOOK_SECRET=whsec_tu_webhook_secret
STRIPE_PRICE_ID_BASIC=price_id_plan_basico
STRIPE_PRICE_ID_PRO=price_id_plan_pro
STRIPE_PRICE_ID_ENTERPRISE=price_id_plan_enterprise

# Tenant configuration
TENANT_DOMAIN_BASE=inventariosmart.com
TENANT_DATABASE_PREFIX=tenant_

# Sanctum (autenticación API)
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,inventariosmart.com,*.inventariosmart.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

## 📋 Estructura del Sistema

### Arquitectura Multi-Tenant

```
┌─────────────────────────────────────────┐
│           Nginx Reverse Proxy           │
│    (Routing por subdominio)             │
└─────────────────────────────────────────┘
                   │
    ┌──────────────┼──────────────┐
    │              │              │
 tenant1      tenant2       tenant3
.tudominio  .tudominio   .tudominio
    │              │              │
    └──────────────┼──────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
   Landlord DB          Tenant DBs
   (usuarios,           (datos aislados
   tenants,                por cliente)
   suscripciones)
```

### Módulos Disponibles

| Módulo | Descripción | Estado |
|--------|-------------|--------|
| Dashboard | Estadísticas y gráficos | ✅ Completo |
| Productos | CRUD + control de stock | ✅ Completo |
| Categorías | Organización jerárquica | ✅ Completo |
| Punto de Venta | Ventas rápidas con POS | ✅ Completo |
| Clientes | Gestión + cuenta corriente | ✅ Completo |
| Proveedores | Gestión + compras | ✅ Completo |
| Cajas | Apertura/cierre/arqueo | ⏳ Pendiente |
| Reportes | Exportaciones y análisis | ⏳ Pendiente |

## 🔧 Comandos Útiles

### Gestión de Tenants

```bash
# Crear nuevo tenant
./crear-tenant.sh slug "Nombre Empresa" admin@email.com password

# Listar tenants
php artisan tenant:list

# Ejecutar comando en tenant específico
php artisan tenant:run tu-slug -- php artisan migrate

# Backup de tenant
php artisan tenant:backup tu-slug
```

### Mantenimiento

```bash
# Ver logs
docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f app

# Limpiar cachés
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Optimizar producción
docker-compose exec app php artisan optimize
docker-compose exec app php artisan route:cache

# Actualizar después de pull
docker-compose exec app composer install --no-dev --optimize-autoloader
docker-compose exec app php artisan migrate --force
```

## 📡 APIs RESTful

### Autenticación

Todas las APIs requieren autenticación vía Sanctum. Incluir header:
```
Authorization: Bearer {token}
Accept: application/json
```

### Endpoints Principales

#### Dashboard
```
GET /api/dashboard/stats          # Estadísticas generales
GET /api/dashboard/ventas-chart   # Datos para gráficos
GET /api/dashboard/top-productos  # Productos más vendidos
GET /api/dashboard/alertas        # Alertas del sistema
```

#### Productos
```
GET    /api/productos             # Listar (con filtros)
POST   /api/productos             # Crear
GET    /api/productos/{id}        # Ver detalle
PUT    /api/productos/{id}        # Actualizar
DELETE /api/productos/{id}        # Eliminar
GET    /api/productos/stats/resumen # Estadísticas
```

**Filtros disponibles:**
- `?search=texto` - Búsqueda por nombre o código
- `?categoria_id=1` - Filtrar por categoría
- `?disponibles=1` - Solo con stock
- `?stock_bajo=1` - Stock bajo mínimo
- `?sin_stock=1` - Sin stock

#### Ventas
```
GET    /api/ventas                # Listar ventas
POST   /api/ventas                # Crear venta
GET    /api/ventas/{id}           # Ver detalle
POST   /api/ventas/{id}/cancelar  # Cancelar venta
GET    /api/ventas/stats/resumen  # Estadísticas
GET    /api/ventas/stats/top-productos # Top productos
```

#### Clientes
```
GET    /api/clientes              # Listar
POST   /api/clientes              # Crear
GET    /api/clientes/{id}         # Ver detalle
PUT    /api/clientes/{id}         # Actualizar
DELETE /api/clientes/{id}         # Eliminar
GET    /api/clientes/stats/resumen # Estadísticas
```

#### Categorías
```
GET    /api/categorias            # Listar
POST   /api/categorias            # Crear
GET    /api/categorias/{id}       # Ver detalle
PUT    /api/categorias/{id}       # Actualizar
DELETE /api/categorias/{id}       # Eliminar
```

#### Proveedores
```
GET    /api/proveedores           # Listar
POST   /api/proveedores           # Crear
GET    /api/proveedores/{id}      # Ver detalle
PUT    /api/proveedores/{id}      # Actualizar
DELETE /api/proveedores/{id}      # Eliminar
GET    /api/proveedores/stats/resumen # Estadísticas
```

## 🎨 Frontend

### Tecnologías
- **Tailwind CSS** - Framework de estilos
- **Alpine.js** - Reactividad JavaScript ligera
- **Chart.js** - Gráficos y visualizaciones
- **Axios** - Peticiones HTTP
- **Laravel Blade Components** - Componentes reutilizables

### Componentes UI Disponibles

```blade
{{-- Botones --}}
<x-button variant="primary">Primario</x-button>
<x-button variant="secondary">Secundario</x-button>
<x-button variant="danger">Peligro</x-button>
<x-button variant="success">Éxito</x-button>
<x-button variant="outline">Outline</x-button>
<x-button variant="ghost">Ghost</x-button>

{{-- Modal --}}
<x-modal id="mi-modal" max-width="lg">
    Contenido del modal
</x-modal>

{{-- Formularios --}}
<x-input name="email" type="email" label="Email" />
<x-select name="tipo" :options="$opciones" label="Tipo" />

{{-- Feedback --}}
<x-alert type="success">Mensaje de éxito</x-alert>
<x-toast position="top-right" />
<x-loading size="md" />

{{-- Datos --}}
<x-card title="Título">Contenido</x-card>
<x-table :headers="$headers" :rows="$rows" />
<x-badge variant="info">Etiqueta</x-badge>
```

## 💳 Sistema de Suscripciones

### Planes Disponibles

| Plan | Precio | Características |
|------|--------|-----------------|
| Básico | $29/mes | 1 usuario, 1000 productos |
| Pro | $79/mes | 5 usuarios, productos ilimitados, soporte |
| Enterprise | $199/mes | Usuarios ilimitados, API completa, soporte prioritario |

### Webhooks de Stripe

Configurar en Stripe Dashboard:
```
https://tudominio.com/api/webhooks/stripe
```

Eventos a escuchar:
- `checkout.session.completed`
- `invoice.paid`
- `invoice.payment_failed`
- `customer.subscription.deleted`

## 🔒 Seguridad

### Características Implementadas
- ✅ Autenticación vía Laravel Sanctum
- ✅ Autorización basada en roles (Spatie Permission)
- ✅ Aislamiento de datos por tenant
- ✅ CSRF protection en formularios
- ✅ Rate limiting en APIs
- ✅ Validación de entrada en todos los endpoints
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)

### Roles de Usuario

| Rol | Permisos |
|-----|----------|
| Admin | Acceso total |
| Vendedor | Ventas, clientes, ver productos |
| Depósito | Gestión de stock, productos |
| Contador | Reportes, cuentas corrientes |

## 🐛 Solución de Problemas

### Error: "Tenant could not be identified"
Verificar que el subdominio esté correctamente configurado en DNS y que el tenant exista en la base de datos landlord.

### Error: "Unauthenticated" en API
Asegurar que:
1. El token Sanctum sea válido
2. Las cookies se envíen correctamente (mismo dominio)
3. SANCTUM_STATEFUL_DOMAINS incluya el dominio

### Error 500 en producción
```bash
# Ver logs
docker-compose exec app tail -f storage/logs/laravel.log

# Permisos
docker-compose exec app chown -R www:www storage bootstrap/cache
```

### Migraciones fallan en tenant
```bash
# Recrear base de datos del tenant
php artisan tenant:recreate-database slug
php artisan tenant:run slug -- php artisan migrate
```

## 📈 Monitoreo

### Logs importantes
```bash
# Errores de aplicación
docker-compose exec app tail -f storage/logs/laravel.log

# Logs de nginx
docker-compose logs -f nginx

# Logs de base de datos
docker-compose logs -f db
```

### Métricas recomendadas
- Tiempo de respuesta de APIs
- Uso de memoria por tenant
- Cantidad de queries por request
- Errores 500/404 por hora

## 🤝 Soporte

Para reportar bugs o solicitar features:
- Email: soporte@inventariosmart.com
- Issues: GitHub Issues
- Documentación: https://docs.inventariosmart.com

---

**Versión:** 1.0.0  
**Última actualización:** Febrero 2026  
**Licencia:** MIT
