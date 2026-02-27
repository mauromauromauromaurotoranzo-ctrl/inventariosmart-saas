#!/bin/bash

# Script para crear un nuevo tenant en InventarioSmart SaaS
# Uso: ./crear-tenant.sh slug "Nombre Empresa" email password [plan]

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Validar argumentos
if [ $# -lt 4 ]; then
    echo -e "${RED}Error: Faltan argumentos${NC}"
    echo "Uso: $0 slug \"Nombre Empresa\" email password [plan]"
    echo ""
    echo "Ejemplo:"
    echo "  $0 mirempresa \"Mi Empresa SA\" admin@mirempresa.com password123 basico"
    exit 1
fi

SLUG=$1
NOMBRE=$2
EMAIL=$3
PASSWORD=$4
PLAN=${5:-"basico"}

# Validar slug (solo letras minúsculas, números y guiones)
if ! [[ $SLUG =~ ^[a-z0-9-]+$ ]]; then
    echo -e "${RED}Error: El slug solo puede contener letras minúsculas, números y guiones${NC}"
    exit 1
fi

# Validar email
if ! [[ $EMAIL =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    echo -e "${RED}Error: Email inválido${NC}"
    exit 1
fi

# Validar plan válido
if ! [[ $PLAN =~ ^(basico|pro|enterprise)$ ]]; then
    echo -e "${RED}Error: Plan debe ser 'basico', 'pro' o 'enterprise'${NC}"
    exit 1
fi

echo -e "${YELLOW}Creando tenant: $NOMBRE ($SLUG)${NC}"
echo "Email: $EMAIL"
echo "Plan: $PLAN"
echo ""

# Verificar que Docker esté corriendo
if ! docker-compose ps | grep -q "app"; then
    echo -e "${RED}Error: Los contenedores no están corriendo${NC}"
    echo "Ejecuta primero: ./deploy.sh produccion"
    exit 1
fi

# Crear tenant usando tinker
docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan tinker <<EOF
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    
    // Crear tenant
    \$tenant = Tenant::create([
        'id' => '$SLUG',
        'name' => '$NOMBRE',
        'slug' => '$SLUG',
        'plan' => '$PLAN',
        'is_active' => true,
        'trial_ends_at' => now()->addDays(14),
    ]);
    
    // Crear dominio
    \$tenant->domains()->create([
        'domain' => '$SLUG.' . env('TENANT_DOMAIN_BASE', 'localhost'),
    ]);
    
    // Crear base de datos del tenant
    \$databaseName = 'tenant_' . \$tenant->id;
    DB::statement("CREATE DATABASE IF NOT EXISTS \$databaseName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Ejecutar migraciones en el tenant
    \$tenant->run(function () {
        Artisan::call('migrate', ['--force' => true]);
    });
    
    // Crear usuario administrador
    \$tenant->run(function () use (\$tenant) {
        \$user = User::create([
            'name' => 'Administrador',
            'email' => '$EMAIL',
            'password' => Hash::make('$PASSWORD'),
            'email_verified_at' => now(),
        ]);
        
        \$user->assignRole('admin');
        
        echo "Usuario creado: $EMAIL\n";
    });
    
    DB::commit();
    
    echo "Tenant creado exitosamente!\n";
    echo "URL: http://$SLUG." . env('TENANT_DOMAIN_BASE', 'localhost') . "\n";
    
} catch (Exception \$e) {
    DB::rollBack();
    echo "Error: " . \$e->getMessage() . "\n";
    exit(1);
}
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Tenant creado exitosamente!${NC}"
    echo ""
    echo "📋 Resumen:"
    echo "   Subdominio: $SLUG.\${TENANT_DOMAIN_BASE}"
    echo "   Admin: $EMAIL"
    echo "   Password: \${PASSWORD}"
    echo "   Plan: $PLAN"
    echo "   Trial: 14 días"
    echo ""
    echo "🚀 Accede a: http://$SLUG.\${TENANT_DOMAIN_BASE}"
else
    echo ""
    echo -e "${RED}❌ Error al crear el tenant${NC}"
    exit 1
fi
